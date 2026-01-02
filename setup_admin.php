<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;

echo "=== Setup Admin User ===\n\n";

// Check existing users
echo "Utilisateurs existants:\n";
$users = User::all(['email', 'first_name', 'last_name']);
foreach ($users as $user) {
    $fullName = trim($user->first_name . ' ' . $user->last_name);
    echo "  - {$user->email} ({$fullName})\n";
}
echo "\n";

// Find or create admin user
$admin = User::where('email', 'admin@demo.be')->first();

if ($admin) {
    echo "✓ Utilisateur admin@demo.be trouvé\n";
    echo "  Mise à jour du mot de passe...\n";
    $admin->update([
        'password' => Hash::make('Demo2024!')
    ]);
    echo "  ✓ Mot de passe mis à jour\n\n";
} else {
    echo "✗ Utilisateur admin@demo.be non trouvé\n";
    echo "  Création du compte...\n";

    $admin = User::create([
        'first_name' => 'Admin',
        'last_name' => 'Demo',
        'email' => 'admin@demo.be',
        'password' => Hash::make('Demo2024!'),
        'email_verified_at' => now(),
        'is_superadmin' => true,
    ]);

    echo "  ✓ Compte créé\n\n";
}

// Attach to first company if exists
$company = Company::first();
if ($company) {
    $admin->companies()->syncWithoutDetaching([
        $company->id => ['role' => 'owner']
    ]);
    echo "✓ Admin attaché à l'entreprise: {$company->name}\n\n";
}

echo "=== CREDENTIALS ===\n";
echo "Email: admin@demo.be\n";
echo "Mot de passe: Demo2024!\n";
echo "Superadmin: Oui\n\n";

echo "✓ Vous pouvez maintenant vous connecter!\n";
