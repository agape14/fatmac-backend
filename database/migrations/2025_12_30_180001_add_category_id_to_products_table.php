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
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('user_id')->constrained()->onDelete('set null');
            $table->decimal('discount_percentage', 5, 2)->nullable()->after('price')->default(0);
            $table->boolean('is_new')->default(false)->after('stock');
            $table->boolean('is_featured')->default(false)->after('is_new');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['category_id', 'discount_percentage', 'is_new', 'is_featured']);
        });
    }
};

