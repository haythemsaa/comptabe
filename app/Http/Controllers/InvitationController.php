<?php

namespace App\Http\Controllers;

use App\Models\InvitationToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class InvitationController extends Controller
{
    /**
     * Show invitation acceptance page.
     */
    public function show(string $token)
    {
        $invitation = InvitationToken::findValid($token);

        if (!$invitation) {
            return view('invitation.expired');
        }

        $invitation->load('company', 'invitedBy', 'user');

        return view('invitation.accept', compact('invitation'));
    }

    /**
     * Accept invitation.
     */
    public function accept(Request $request, string $token)
    {
        $invitation = InvitationToken::findValid($token);

        if (!$invitation) {
            return redirect()->route('login')
                ->with('error', 'Cette invitation a expiré ou est invalide.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Update user with name and password
        $user = $invitation->user;
        $user->update([
            'name' => $validated['name'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(), // Auto-verify on invitation
        ]);

        // Accept invitation
        $invitation->accept();

        // Attach user to company if specified
        if ($invitation->company_id) {
            $user->companies()->syncWithoutDetaching([
                $invitation->company_id => [
                    'role' => $invitation->role,
                ],
            ]);

            // Set as current tenant
            session(['current_tenant_id' => $invitation->company_id]);
        }

        // Auto-login
        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Bienvenue ! Votre compte a été activé avec succès.');
    }

}
