<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_plugins', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('type', 20)->default('module');
            $table->string('slug', 140)->unique();
            $table->string('feature_key', 140)->unique();
            $table->string('version', 40)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        $items = [
            ['Website Home', 'module', 'website-home', 'home', '1.0.0', 'Public homepage and server overview.', true, 1],
            ['News', 'module', 'news', 'news', '1.0.0', 'News listing, article pages and homepage news cards.', true, 2],
            ['Server Information', 'module', 'server-information', 'info', '1.0.0', 'Server information page.', true, 3],
            ['Download Center', 'module', 'download-center', 'download', '1.0.0', 'Game client and patch download page.', true, 4],
            ['Donation', 'module', 'donation', 'donation', '1.0.0', 'Donation options page.', true, 5],
            ['Support', 'module', 'support', 'support', '1.0.0', 'Support and FAQ page.', true, 6],
            ['Account Registration', 'module', 'account-registration', 'account.registration', '1.0.0', 'AION account registration.', true, 7],
            ['Account Login', 'module', 'account-login', 'account.login', '1.0.0', 'AION account login.', true, 8],
            ['Character Manager', 'module', 'character-manager', 'account.characters', '1.0.0', 'Player character list.', true, 9],
            ['Change Password', 'module', 'change-password', 'account.password', '1.0.0', 'AION account password change.', true, 10],
            ['Server Status API', 'module', 'server-status-api', 'server.status', '1.0.0', 'Public server status JSON endpoint.', true, 11],
        ];

        foreach ($items as [$name, $type, $slug, $key, $version, $description, $enabled, $sort]) {
            DB::table('module_plugins')->insert([
                'name' => $name, 'type' => $type, 'slug' => $slug, 'feature_key' => $key,
                'version' => $version, 'description' => $description, 'is_enabled' => $enabled,
                'sort_order' => $sort, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('module_plugins');
    }
};
