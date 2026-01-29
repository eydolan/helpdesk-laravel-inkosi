<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::updateOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
        ]);

        Role::updateOrCreate([
            'name' => 'Admin Unit',
            'guard_name' => 'web',
        ]);

        Role::updateOrCreate([
            'name' => 'Staff Unit',
            'guard_name' => 'web',
        ]);

        Role::updateOrCreate([
            'name' => 'Global Viewer',
            'guard_name' => 'web',
        ]);

        Role::updateOrCreate([
            'name' => 'Unit Viewer',
            'guard_name' => 'web',
        ]);

        Role::updateOrCreate([
            'name' => 'Global Staff',
            'guard_name' => 'web',
        ]);

        Role::updateOrCreate([
            'name' => 'Customer',
            'guard_name' => 'web',
        ]);
    }
}
