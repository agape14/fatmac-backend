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
        Schema::table('users', function (Blueprint $table) {
            $table->string('whatsapp_number')->nullable()->after('phone_number');
            $table->string('yape_qr')->nullable()->after('whatsapp_number');
            $table->string('plin_qr')->nullable()->after('yape_qr');
            $table->text('business_description')->nullable()->after('plin_qr');
            $table->string('business_address')->nullable()->after('business_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_number',
                'yape_qr',
                'plin_qr',
                'business_description',
                'business_address'
            ]);
        });
    }
};

