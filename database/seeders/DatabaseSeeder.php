<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Role
        $superAdminRole = \Spatie\Permission\Models\Role::create(['name' => 'super-admin']);
        $adminRole = \Spatie\Permission\Models\Role::create(['name' => 'admin']);

        // Permissions
        $dashboardPermission = \Spatie\Permission\Models\Permission::create(['name' => 'dashboard-page']);
        $userPagePermission = \Spatie\Permission\Models\Permission::create(['name' => 'user-page']);
        $userCreatePermission = \Spatie\Permission\Models\Permission::create(['name' => 'user-create']);
        $userEditPermission = \Spatie\Permission\Models\Permission::create(['name' => 'user-edit']);
        $userDeletePermission = \Spatie\Permission\Models\Permission::create(['name' => 'user-delete']);
        $optionRolePermission = \Spatie\Permission\Models\Permission::create(['name' => 'option-role']);

        // Assign permissions to roles
        $superAdminRole->givePermissionTo([
            $dashboardPermission,
            $userPagePermission,
            $userCreatePermission,
            $userEditPermission,
            $userDeletePermission,
            $optionRolePermission
        ]);

        $adminRole->givePermissionTo([
            $dashboardPermission, $userPagePermission, $userCreatePermission
        ]);

        // Create users
        User::factory()->create(['name' => 'Super Admin', 'email' => 'superadmin@example.com', 'password' => Hash::make('password')])->assignRole($superAdminRole);
        User::factory(10)->create()->each(function ($user, $index) use ($adminRole) {
            $user->name = "Admin $index";
            $user->email = "admin$index@example.com";
            $user->password = Hash::make('password');
            $user->save();
            $user->assignRole($adminRole);
        });
    }
}
