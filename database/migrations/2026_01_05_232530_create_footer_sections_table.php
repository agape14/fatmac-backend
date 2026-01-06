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
        Schema::create('footer_sections', function (Blueprint $table) {
            $table->id();
            $table->integer('position')->default(1); // 1, 2, 3, 4 para las 4 secciones
            $table->string('title')->nullable();
            $table->text('content')->nullable(); // JSON o texto para el contenido
            $table->string('logo_url')->nullable(); // Para la sección 1
            $table->text('description')->nullable(); // Para la sección 1
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->json('links')->nullable(); // Array de links para las otras secciones
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('footer_sections');
    }
};
