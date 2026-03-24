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
        Schema::table('messages_swift', function (Blueprint $table) {
            // ✅ Ajouter les colonnes de timestamp manquantes
            $table->timestamp('CREATED_AT')->nullable();
            $table->timestamp('UPDATED_AT')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages_swift', function (Blueprint $table) {
            $table->dropColumn(['CREATED_AT', 'UPDATED_AT']);
        });
    }
};