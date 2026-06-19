<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Create (or promote) the admin account from ADMIN_EMAIL / ADMIN_PASSWORD env vars.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@rcpcanarias.test');
        $password = env('ADMIN_PASSWORD', 'password');

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_NAME', 'Administrator'),
                'password' => Hash::make($password),
                'role' => 'admin',
            ]
        );

        $this->command?->info("Admin ready: {$user->email} (role: {$user->role})");
    }
}
