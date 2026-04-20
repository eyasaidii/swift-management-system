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
        // Colonne déjà nullable dans la migration de création — skip si Oracle
        if (config('database.default') === 'oracle') {
            return;
        }
        Schema::table('messages_swift', function (Blueprint $table) {
            $table->unsignedBigInteger('IMPORT_JOB_ID')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages_swift', function (Blueprint $table) {
            $table->unsignedBigInteger('IMPORT_JOB_ID')->nullable(false)->change();
        });
    }
};
