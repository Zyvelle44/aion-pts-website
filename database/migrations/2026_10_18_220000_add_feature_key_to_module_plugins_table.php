<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('module_plugins')) {
            // The original create migration will create the table when migrations are run in order.
            return;
        }

        if (! Schema::hasColumn('module_plugins', 'feature_key')) {
            Schema::table('module_plugins', function (Blueprint $table): void {
                // Nullable first so existing rows can be backfilled safely on SQL Server.
                $table->string('feature_key', 140)->nullable();
            });
        }

        $featureKeysBySlug = [
            'website-home' => 'home',
            'home' => 'home',
            'news' => 'news',
            'server-information' => 'info',
            'server-info' => 'info',
            'download-center' => 'download',
            'download' => 'download',
            'donation' => 'donation',
            'support' => 'support',
            'account-registration' => 'account.registration',
            'account-login' => 'account.login',
            'character-manager' => 'account.characters',
            'change-password' => 'account.password',
            'server-status-api' => 'server.status',
        ];

        $rows = DB::table('module_plugins')->select('id', 'slug', 'feature_key')->get();
        $used = [];

        foreach ($rows as $row) {
            $existing = is_string($row->feature_key) ? trim($row->feature_key) : '';
            $slug = is_string($row->slug) ? trim($row->slug) : '';
            $key = $existing !== '' ? $existing : ($featureKeysBySlug[$slug] ?? str_replace('-', '.', $slug));
            if ($key === '') {
                $key = 'module.' . $row->id;
            }

            // Keep keys unique if legacy rows happen to have duplicate/missing slugs.
            if (isset($used[$key])) {
                $key = substr($key, 0, 120) . '.' . $row->id;
            }
            $used[$key] = true;

            DB::table('module_plugins')->where('id', $row->id)->update([
                'feature_key' => $key,
                'updated_at' => now(),
            ]);
        }

        // Add the unique index only if the expected index is not already present.
        // SQL Server index names are schema-wide, so use a specific name.
        try {
            Schema::table('module_plugins', function (Blueprint $table): void {
                $table->unique('feature_key', 'module_plugins_feature_key_unique');
            });
        } catch (\Throwable $e) {
            // An equivalent index may already exist from a partially applied deployment.
            // Do not block the repair when the column/data are now usable.
            if (! Schema::hasColumn('module_plugins', 'feature_key')) {
                throw $e;
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('module_plugins') && Schema::hasColumn('module_plugins', 'feature_key')) {
            try {
                Schema::table('module_plugins', function (Blueprint $table): void {
                    $table->dropUnique('module_plugins_feature_key_unique');
                });
            } catch (\Throwable $e) {
                // Ignore when the index does not exist.
            }

            Schema::table('module_plugins', function (Blueprint $table): void {
                $table->dropColumn('feature_key');
            });
        }
    }
};
