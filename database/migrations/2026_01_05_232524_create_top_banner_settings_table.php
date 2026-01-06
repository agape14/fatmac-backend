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
        Schema::create('top_banner_settings', function (Blueprint $table) {
            $table->id();
            $table->string('text')->default('ENVÍO GRATIS DESDE S/79');
            $table->string('background_color')->default('#3B82F6'); // blue-500 por defecto
            $table->string('text_color')->default('#FFFFFF'); // blanco por defecto
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('top_banner_settings');
    }
};
