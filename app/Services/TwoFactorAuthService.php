<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TwoFactorAuthService
{
    /**
     * Generate a new secret key for 2FA.
     */
    public function generateSecret(): string
    {
        return $this->generateBase32Secret(16);
    }

    /**
     * Generate a base32 encoded secret.
     */
    protected function generateBase32Secret(int $length = 16): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';

        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }

        return $secret;
    }

    /**
     * Get the QR code URL for Google Authenticator.
     */
    public function getQrCodeUrl(User $user, string $secret): string
    {
        $appName = urlencode(config('app.name', 'ComptaBE'));
        $email = urlencode($user->email);

        return "otpauth://totp/{$appName}:{$email}?secret={$secret}&issuer={$appName}&algorithm=SHA1&digits=6&period=30";
    }

    /**
     * Generate QR code SVG for the secret.
     */
    public function generateQrCodeSvg(User $user, string $secret): string
    {
        $url = $this->getQrCodeUrl($user, $secret);

        // If QrCode package is available
        if (class_exists('SimpleSoftwareIO\QrCode\Facades\QrCode')) {
            return QrCode::format('svg')
                ->size(200)
                ->errorCorrection('M')
                ->generate($url);
        }

        // Fallback: return URL for external QR code service
        return $url;
    }

    /**
     * Verify a TOTP code against the secret.
     */
    public function verifyCode(string $secret, string $code): bool
    {
        // Allow for time drift (1 period before and after)
        $timeSlices = [
            floor(time() / 30) - 1,
            floor(time() / 30),
            floor(time() / 30) + 1,
        ];

        foreach ($timeSlices as $timeSlice) {
            $expectedCode = $this->generateTOTP($secret, $timeSlice);
            if (hash_equals($expectedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate a TOTP code for a given time slice.
     */
    protected function generateTOTP(string $secret, int $timeSlice): string
    {
        // Decode base32 secret
        $secretKey = $this->base32Decode($secret);

        // Pack time into binary string
        $time = pack('N*', 0) . pack('N*', $timeSlice);

        // Generate HMAC-SHA1
        $hmac = hash_hmac('sha1', $time, $secretKey, true);

        // Get offset from last nibble
        $offset = ord(substr($hmac, -1)) & 0x0F;

        // Get 4 bytes starting at offset
        $binary = (
            ((ord($hmac[$offset]) & 0x7F) << 24) |
            ((ord($hmac[$offset + 1]) & 0xFF) << 16) |
            ((ord($hmac[$offset + 2]) & 0xFF) << 8) |
            (ord($hmac[$offset + 3]) & 0xFF)
        );

        // Generate 6-digit code
        $code = $binary % 1000000;

        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Decode a base32 string.
     */
    protected function base32Decode(string $input): string
    {
        $map = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $input = strtoupper($input);
        $input = str_replace('=', '', $input);

        $output = '';
        $buffer = 0;
        $bitsLeft = 0;

        for ($i = 0; $i < strlen($input); $i++) {
            $val = strpos($map, $input[$i]);
            if ($val === false) {
                continue;
            }

            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $output;
    }

    /**
     * Enable 2FA for a user.
     */
    public function enableTwoFactor(User $user, string $secret): void
    {
        $user->update([
            'mfa_secret' => encrypt($secret),
            'mfa_enabled' => true,
        ]);
    }

    /**
     * Disable 2FA for a user.
     */
    public function disableTwoFactor(User $user): void
    {
        $user->update([
            'mfa_secret' => null,
            'mfa_enabled' => false,
        ]);
    }

    /**
     * Get the decrypted secret for a user.
     */
    public function getSecret(User $user): ?string
    {
        if (!$user->mfa_secret) {
            return null;
        }

        return decrypt($user->mfa_secret);
    }

    /**
     * Generate recovery codes for a user.
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $codes[] = Str::upper(Str::random(4) . '-' . Str::random(4));
        }

        return $codes;
    }
}
