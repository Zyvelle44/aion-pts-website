<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('installed_plugins')) return;

        Schema::create('installed_plugins', function (Blueprint $table): void {
            $table->id();
            $table->string('plugin_id', 60)->unique();
            $table->string('name', 120);
            $table->string('version', 40);
            $table->string('type', 20);
            $table->string('description', 500)->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->longText('manifest')->nullable();
            $table->string('installed_path', 1000);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Retain plugin registry and package metadata on rollback to avoid accidental data loss.
    }
};
