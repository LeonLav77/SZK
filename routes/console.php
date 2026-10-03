<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;

Artisan::command('szk:admin {email} {--name=Admin}', function (string $email) {
    $password = $this->secret('Lozinka (barem 8 znakova)');

    if (strlen((string) $password) < 8) {
        $this->error('Lozinka mora imati barem 8 znakova.');

        return 1;
    }

    $user = User::firstOrNew(['email' => $email]);
    $user->name = $user->name ?: $this->option('name');
    $user->password = $password;
    $user->is_admin = true;
    $user->save();

    $this->info("Admin {$email} je spreman. Prijava: /admin");
})->purpose('Create an admin user (or make an existing user admin)');
