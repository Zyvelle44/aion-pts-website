<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('module_plugins')) {
            return;
        }

        Schema::table('module_plugins', function (Blueprint $table): void {
            if (! Schema::hasColumn('module_plugins', 'dependencies')) {
                $table->text('dependencies')->nullable();
            }
            if (! Schema::hasColumn('module_plugins', 'min_app_version')) {
                $table->string('min_app_version', 40)->nullable();
            }
            if (! Schema::hasColumn('module_plugins', 'max_app_version')) {
                $table->string('max_app_version', 40)->nullable();
            }
        });
    }

    public function down(): void
    {
        // Intentionally retain compatibility metadata on rollback to avoid losing admin configuration.
    }
};
