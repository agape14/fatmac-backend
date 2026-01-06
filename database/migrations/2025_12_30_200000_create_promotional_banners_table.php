<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('promotional_banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('button_text')->default('VER AHORA');
            $table->string('button_link')->nullable();
            $table->string('image_left_url')->nullable();
            $table->string('image_right_url')->nullable();
            $table->string('background_color')->default('from-pink-pastel via-purple-pastel to-yellow-pastel');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotional_banners');
    }
};

