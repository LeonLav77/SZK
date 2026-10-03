<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/** Local development data. Production admins are made with `php artisan szk:admin`. */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::factory()->create(['name' => 'Damir', 'email' => 'admin@szk.test']);
        $admin->forceFill(['is_admin' => true])->save();

        User::factory()->create(['name' => 'Test Student', 'email' => 'student@szk.test']);

        $this->call(Edition2026Seeder::class);
    }
}
