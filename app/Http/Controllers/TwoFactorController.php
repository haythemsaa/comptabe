<?php

namespace App\Http\Controllers;

use App\Services\TwoFactorAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorController extends Controller
{
    public function __construct(
        protected TwoFactorAuthService $twoFactorService
    ) {}

    /**
     * Show 2FA setup page.
     */
    public function setup()
    {
        $user = Auth::user();

        if ($user->mfa_enabled) {
            return redirect()->route('settings.security')
                ->with('info', 'L\'authentification à deux facteurs est déjà activée.');
        }

        // Generate a new secret
        $secret = $this->twoFactorService->generateSecret();

        // Store temporarily in session
        session(['2fa_secret' => $secret]);

        // Generate QR code URL
        $qrCodeUrl = $this->twoFactorService->getQrCodeUrl($user, $secret);

        return view('auth.two-factor.setup', [
            'secret' => $secret,
            'qrCodeUrl' => $qrCodeUrl,
        ]);
    }

    /**
     * Enable 2FA after verification.
     */
    public function enable(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $secret = session('2fa_secret');

        if (!$secret) {
            return back()->withErrors(['code' => 'Session expirée. Veuillez recommencer.']);
        }

        if (!$this->twoFactorService->verifyCode($secret, $request->code)) {
            return back()->withErrors(['code' => 'Le code est invalide. Veuillez réessayer.']);
        }

        // Enable 2FA
        $this->twoFactorService->enableTwoFactor(Auth::user(), $secret);

        // Generate recovery codes
        $recoveryCodes = $this->twoFactorService->generateRecoveryCodes();

        // Store recovery codes (encrypted)
        Auth::user()->update([
            'recovery_codes' => encrypt(json_encode($recoveryCodes)),
        ]);

        // Clear session
        session()->forget('2fa_secret');

        return view('auth.two-factor.recovery-codes', [
            'recoveryCodes' => $recoveryCodes,
        ]);
    }

    /**
     * Show 2FA challenge page (during login).
     */
    public function challenge()
    {
        if (!session('2fa_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor.challenge');
    }

    /**
     * Verify 2FA code during login.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $userId = session('2fa_user_id');
        $remember = session('2fa_remember', false);

        if (!$userId) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Session expirée. Veuillez vous reconnecter.']);
        }

        $user = \App\Models\User::find($userId);

        if (!$user) {
            return redirect()->route('login');
        }

        $secret = $this->twoFactorService->getSecret($user);

        // Check if it's a recovery code
        if (strlen($request->code) === 9 && str_contains($request->code, '-')) {
            if ($this->verifyRecoveryCode($user, $request->code)) {
                return $this->completeLogin($user, $remember, $request);
            }
            return back()->withErrors(['code' => 'Code de récupération invalide.']);
        }

        // Verify TOTP code
        if (!$this->twoFactorService->verifyCode($secret, $request->code)) {
            return back()->withErrors(['code' => 'Le code est invalide. Veuillez réessayer.']);
        }

        return $this->completeLogin($user, $remember, $request);
    }

    /**
     * Complete the login process after 2FA verification.
     */
    protected function completeLogin($user, $remember, $request)
    {
        // Clear 2FA session data
        session()->forget(['2fa_user_id', '2fa_remember']);

        // Login the user
        Auth::login($user, $remember);

        // Regenerate session
        $request->session()->regenerate();

        // Update last login
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Verify a recovery code.
     */
    protected function verifyRecoveryCode($user, string $code): bool
    {
        if (!$user->recovery_codes) {
            return false;
        }

        $codes = json_decode(decrypt($user->recovery_codes), true);

        if (!is_array($codes)) {
            return false;
        }

        $code = strtoupper($code);

        if (in_array($code, $codes)) {
            // Remove used code
            $codes = array_values(array_diff($codes, [$code]));
            $user->update([
                'recovery_codes' => encrypt(json_encode($codes)),
            ]);
            return true;
        }

        return false;
    }

    /**
     * Disable 2FA.
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $this->twoFactorService->disableTwoFactor(Auth::user());

        Auth::user()->update([
            'recovery_codes' => null,
        ]);

        return redirect()->route('settings.security')
            ->with('success', 'L\'authentification à deux facteurs a été désactivée.');
    }

    /**
     * Show recovery codes.
     */
    public function showRecoveryCodes(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();

        if (!$user->recovery_codes) {
            return back()->with('error', 'Aucun code de récupération disponible.');
        }

        $codes = json_decode(decrypt($user->recovery_codes), true);

        return view('auth.two-factor.recovery-codes', [
            'recoveryCodes' => $codes,
            'showBackLink' => true,
        ]);
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $codes = $this->twoFactorService->generateRecoveryCodes();

        Auth::user()->update([
            'recovery_codes' => encrypt(json_encode($codes)),
        ]);

        return view('auth.two-factor.recovery-codes', [
            'recoveryCodes' => $codes,
            'regenerated' => true,
        ]);
    }
}
