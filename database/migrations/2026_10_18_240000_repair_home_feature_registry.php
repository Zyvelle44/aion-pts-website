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
        $home = DB::table('module_plugins')
            ->where('feature_key', 'home')
            ->orWhere('slug', 'website-home')
            ->first();

        if ($home) {
            DB::table('module_plugins')->where('id', $home->id)->update([
                'name' => 'Website Home',
                'type' => $home->type ?? 'module',
                'slug' => $home->slug ?: 'website-home',
                'feature_key' => 'home',
                'is_enabled' => true,
                'updated_at' => $now,
            ]);

            return;
        }

        $data = [
            'name' => 'Website Home',
            'type' => 'module',
            'slug' => 'website-home',
            'feature_key' => 'home',
            'is_enabled' => true,
            'sort_order' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        foreach (['version' => '1.0.0', 'description' => 'Public homepage and server overview.'] as $column => $value) {
            if (Schema::hasColumn('module_plugins', $column)) {
                $data[$column] = $value;
            }
        }

        DB::table('module_plugins')->insert($data);
    }

    public function down(): void
    {
        // Intentionally keep the home feature record to avoid breaking the public site on rollback.
    }
};
