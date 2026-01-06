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
        Schema::table('orders', function (Blueprint $table) {
            // Hacer customer_id nullable para permitir pedidos sin usuario registrado
            $table->foreignId('customer_id')->nullable()->change();
            
            // Agregar campos de información del cliente para pedidos sin autenticación
            $table->string('customer_name')->nullable()->after('customer_id');
            $table->string('customer_email')->nullable()->after('customer_name');
            $table->string('customer_phone')->nullable()->after('customer_email');
            $table->text('customer_address')->nullable()->after('customer_phone');
            $table->string('payment_method')->nullable()->after('voucher_image'); // 'yape' o 'plin'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['customer_name', 'customer_email', 'customer_phone', 'customer_address', 'payment_method']);
            // Nota: No revertimos el cambio de nullable en customer_id para evitar problemas
        });
    }
};

