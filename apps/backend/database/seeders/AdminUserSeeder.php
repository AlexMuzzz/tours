<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            [
                'email' => 'admin@example.com',
                'name' => 'Admin',
            ],
            [
                'email' => 'editor@example.com',
                'name' => 'Editor Admin',
            ],
            [
                'email' => 'operator@example.com',
                'name' => 'Operator Admin',
            ],
        ];

        foreach ($admins as $admin) {
            User::query()->updateOrCreate(
                ['email' => $admin['email']],
                [
                    'name' => $admin['name'],
                    'password' => 'password',
                    'role' => UserRole::ADMIN,
                ]
            );
        }
    }
}
