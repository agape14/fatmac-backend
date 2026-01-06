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
        Schema::create('home_banners', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable(); // "Colección"
            $table->string('subtitle')->nullable(); // "VESTIDOS"
            $table->string('button_text')->default('VER AHORA');
            $table->string('button_link')->nullable();
            $table->string('background_image_url')->nullable();
            $table->string('background_color')->nullable(); // Color de fondo si no hay imagen
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
        Schema::dropIfExists('home_banners');
    }
};
