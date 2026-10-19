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
            return;
        }

        // Add the column only when missing; safe for databases where the patch was partially applied.
        if (! Schema::hasColumn('module_plugins', 'is_enabled')) {
            Schema::table('module_plugins', function (Blueprint $table): void {
                // Keep existing features available by default. The old registry used is_active.
                $table->boolean('is_enabled')->default(true);
            });
        }

        // Preserve the existing toggle state if the original column is present.
        if (Schema::hasColumn('module_plugins', 'is_active')) {
            DB::table('module_plugins')->update([
                'is_enabled' => DB::raw('[is_active]'),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('module_plugins') && Schema::hasColumn('module_plugins', 'is_enabled')) {
            Schema::table('module_plugins', function (Blueprint $table): void {
                $table->dropColumn('is_enabled');
            });
        }
    }
};
