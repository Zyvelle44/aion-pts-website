<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donation_options', function (Blueprint $table) {
            $table->id();
            $table->string('title', 180);
            $table->string('method', 100);
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->string('payment_url', 2048)->nullable();
            $table->boolean('is_active')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donation_options');
    }
};
