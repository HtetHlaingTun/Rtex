<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@luckey.com',
            'password' => Hash::make('password'), // added \
            'role' => 'super_admin',
            'is_active' => true,
            'phone' => '09123456789',
            'permissions' => ['create_rates', 'edit_rates', 'view_reports', 'view_dashboard'],
            'department' => 'Management',
            'email_verified_at' => now(),
            'notify_on_verification' => true,
            'notify_on_new_entry' => true,
            'notify_on_rejection' => true,
        ]);

        // 2. Create Admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@luckey.com',
            'password' => Hash::make('password'), // added \
            'role' => 'admin',
            'is_active' => true,
            'phone' => '09987654321',
            'department' => 'Operations',
            'email_verified_at' => now(),
            'created_by' => $superAdmin->id,
            'notify_on_verification' => true,
            'notify_on_new_entry' => true,
            'notify_on_rejection' => true,
        ]);

        // 3. Create Editor
        $editor = User::create([
            'name' => 'Editor User',
            'email' => 'editor@luckey.com',
            'password' => Hash::make('password'), // added \
            'role' => 'editor',
            'is_active' => true,
            'phone' => '09777777777',
            'department' => 'Data Entry',
            'email_verified_at' => now(),
            'created_by' => $admin->id,

            'notify_on_verification' => true,
            'notify_on_new_entry' => false,
            'notify_on_rejection' => true,
        ]);

        // 4. Create Viewer
        User::create([
            'name' => 'Viewer User',
            'email' => 'viewer@luckey.com',
            'password' => Hash::make('password'), // added \
            'role' => 'viewer',
            'is_active' => true,
            'phone' => '09555555555',
            'department' => 'Guest',
            'email_verified_at' => now(),
            'created_by' => $admin->id,
            'permissions' => ['view_dashboard'],
        ]);

        // 5. Update self-reference
        $superAdmin->created_by = $superAdmin->id;
        $superAdmin->save();
    }
}
