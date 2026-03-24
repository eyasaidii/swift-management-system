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
            // Rendre IMPORT_JOB_ID nullable pour Oracle
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