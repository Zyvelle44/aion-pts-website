<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('module_plugins')
            || ! Schema::hasColumn('module_plugins', 'feature_key')
            || ! Schema::hasColumn('module_plugins', 'is_enabled')) {
            return;
        }

        $now = now();
        $features = [
            ['Website Home', 'module', 'website-home', 'home', 'Public homepage and server overview.', 1],
            ['News', 'module', 'news', 'news', 'News listing, article pages and homepage news cards.', 2],
            ['Server Information', 'module', 'server-information', 'info', 'Server information page.', 3],
            ['Download Center', 'module', 'download-center', 'download', 'Game client and patch download page.', 4],
            ['Donation', 'module', 'donation', 'donation', 'Donation options page.', 5],
            ['Support', 'module', 'support', 'support', 'Support and FAQ page.', 6],
            ['Account Registration', 'module', 'account-registration', 'account.registration', 'AION account registration.', 7],
            ['Account Login', 'module', 'account-login', 'account.login', 'AION account login.', 8],
            ['Character Manager', 'module', 'character-manager', 'account.characters', 'Player character list.', 9],
            ['Change Password', 'module', 'change-password', 'account.password', 'AION account password change.', 10],
            ['Server Status API', 'module', 'server-status-api', 'server.status', 'Public server status JSON endpoint.', 11],
        ];

        foreach ($features as [$name, $type, $slug, $key, $description, $sort]) {
            $existsByKey = DB::table('module_plugins')->where('feature_key', $key)->exists();
            $existsBySlug = Schema::hasColumn('module_plugins', 'slug')
                && DB::table('module_plugins')->where('slug', $slug)->exists();

            if ($existsByKey || $existsBySlug) {
                continue;
            }

            // Some project versions have a separate unique module_key column.
            // Give every inserted built-in row a non-null unique value to avoid
            // SQL Server's single-NULL unique-index behavior.
            if (Schema::hasColumn('module_plugins', 'module_key')
                && DB::table('module_plugins')->where('module_key', $slug)->exists()) {
                continue;
            }

            $row = [
                'name' => $name,
                'type' => $type,
                'feature_key' => $key,
                'is_enabled' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (Schema::hasColumn('module_plugins', 'slug')) $row['slug'] = $slug;
            if (Schema::hasColumn('module_plugins', 'module_key')) $row['module_key'] = $slug;
            if (Schema::hasColumn('module_plugins', 'description')) $row['description'] = $description;
            if (Schema::hasColumn('module_plugins', 'version')) $row['version'] = '1.0.0';
            if (Schema::hasColumn('module_plugins', 'sort_order')) $row['sort_order'] = $sort;

            DB::table('module_plugins')->insert($row);
        }
    }

    public function down(): void
    {
        // Intentionally retain registry rows; admins may have edited them after migration.
    }
};
