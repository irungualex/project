<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('civicdesk.admin.email');
        $password = config('civicdesk.admin.password');

        if (! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)
            || ! is_string($password) || strlen($password) < 12) {
            throw new RuntimeException('Set ADMIN_EMAIL and a unique ADMIN_PASSWORD of at least 12 characters in .env before seeding.');
        }

        User::updateOrCreate(
            ['email' => $email],
            ['name' => 'CivicDesk Administrator', 'password' => $password, 'role' => 'admin'],
        );
    }
}
