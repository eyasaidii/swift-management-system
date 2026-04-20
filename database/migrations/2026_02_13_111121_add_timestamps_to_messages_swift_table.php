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
        // Ajouter les colonnes seulement si elles n'existent pas (compatibilité SQLite)
        if (! Schema::hasColumn('messages_swift', 'CREATED_AT') || ! Schema::hasColumn('messages_swift', 'UPDATED_AT')) {
            Schema::table('messages_swift', function (Blueprint $table) {
                if (! Schema::hasColumn('messages_swift', 'CREATED_AT')) {
                    $table->timestamp('CREATED_AT')->nullable();
                }
                if (! Schema::hasColumn('messages_swift', 'UPDATED_AT')) {
                    $table->timestamp('UPDATED_AT')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('messages_swift', 'CREATED_AT') || Schema::hasColumn('messages_swift', 'UPDATED_AT')) {
            Schema::table('messages_swift', function (Blueprint $table) {
                if (Schema::hasColumn('messages_swift', 'CREATED_AT')) {
                    $table->dropColumn('CREATED_AT');
                }
                if (Schema::hasColumn('messages_swift', 'UPDATED_AT')) {
                    $table->dropColumn('UPDATED_AT');
                }
            });
        }
    }
};
