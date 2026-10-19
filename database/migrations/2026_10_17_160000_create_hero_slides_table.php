<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->string('eyebrow', 120)->default('A WORLD BEYOND THE SKY');
            $table->string('title', 120);
            $table->string('highlight', 120)->nullable();
            $table->text('description');
            $table->string('image_path', 2048)->default('hero-slides/aion-hero.jpg');
            $table->string('primary_label', 80)->default('EXPLORE DOWNLOADS');
            $table->string('primary_url', 2048)->default('/download');
            $table->string('secondary_label', 80)->default('LATEST CHRONICLES');
            $table->string('secondary_url', 2048)->default('/news');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        DB::table('hero_slides')->insert([
            'eyebrow' => 'A WORLD BEYOND THE SKY',
            'title' => 'AION PTS',
            'highlight' => 'BY ZYVALLE',
            'description' => 'Discover the timeless world of Atreia. Two factions, one destiny. Begin your journey in AION 4.6 PTS.',
            'image_path' => 'hero-slides/aion-hero.jpg',
            'primary_label' => 'EXPLORE DOWNLOADS',
            'primary_url' => '/download',
            'secondary_label' => 'LATEST CHRONICLES',
            'secondary_url' => '/news',
            'sort_order' => 0,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_slides');
    }
};
