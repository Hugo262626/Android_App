<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions
        Permission::create(['name' => 'delete users']);
        Permission::create(['name' => 'view users']);

        // Rôle admin
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(['delete users', 'view users']);

        // Rôle utilisateur
        $user = Role::create(['name' => 'user']);
    }
}
