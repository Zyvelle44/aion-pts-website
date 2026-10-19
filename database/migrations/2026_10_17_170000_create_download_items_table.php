<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('download_items', function (Blueprint $table) {
            $table->id();
            $table->string('title', 180);
            $table->string('slug', 200)->unique();
            $table->string('type', 40)->default('game_client')->index();
            $table->string('version', 80)->nullable();
            $table->text('description')->nullable();
            $table->string('download_url', 2048)->nullable();
            $table->unsignedBigInteger('file_size_bytes')->nullable();
            $table->string('sha256', 64)->nullable();
            $table->text('release_notes')->nullable();
            $table->boolean('is_active')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('download_items');
    }
};
