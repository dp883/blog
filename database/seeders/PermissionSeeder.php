<?php

namespace Database\Seeders;

use App\Models\User;
use Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',

            'posts.view',
            'posts.create',
            'posts.edit',
            'posts.delete',

            'categories.view',
            'categories.create',
            'categories.edit',
            'categories.delete',

            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',

            'tags.view',
            'tags.create',
            'tags.edit',
            'tags.delete',

            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            'comments.view',
            'comments.approve',
            'comments.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
        $adminRole->syncPermissions(
            Permission::where('guard_name', 'web')->get()
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('12345678'),
            ]
        );

        $admin->syncRoles([$adminRole]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
