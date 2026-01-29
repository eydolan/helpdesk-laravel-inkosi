<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. create a super admin
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->syncRoles('Super Admin');

        // 2. create a admin unit
        $adminUnit = User::updateOrCreate(
            ['email' => 'adminunit@example.com'],
            [
                'name' => 'Admin Unit',
                'password' => bcrypt('password'),
                'unit_id' => 1,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $adminUnit->syncRoles('Admin Unit');

        // 3. create a staff unit
        $staffUnit = User::updateOrCreate(
            ['email' => 'staffunit@example.com'],
            [
                'name' => 'Staff Unit',
                'password' => bcrypt('password'),
                'unit_id' => 1,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $staffUnit->syncRoles('Staff Unit');

        // 4. create a user
        $user = User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'User',
                'password' => bcrypt('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
