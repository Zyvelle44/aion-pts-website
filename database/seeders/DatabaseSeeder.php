<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';

        $superAdmin = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => $guard,
        ]);

        $newsEditor = Role::firstOrCreate([
            'name' => 'news_editor',
            'guard_name' => $guard,
        ]);

        $viewer = Role::firstOrCreate([
            'name' => 'viewer',
            'guard_name' => $guard,
        ]);

        // Super Admin receives all generated permissions.
        $superAdmin->syncPermissions(
            Permission::where('guard_name', $guard)->get()
        );

        // News Editor can view, create, and update News only.
        $newsEditor->syncPermissions(
            Permission::where('guard_name', $guard)
                ->whereIn('name', [
                    'ViewAny:News',
                    'View:News',
                    'Create:News',
                    'Update:News',
                ])
                ->get()
        );

        // Viewer can only view News.
        $viewer->syncPermissions(
            Permission::where('guard_name', $guard)
                ->whereIn('name', [
                    'ViewAny:News',
                    'View:News',
                ])
                ->get()
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}