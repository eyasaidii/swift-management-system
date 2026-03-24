<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages_swift', function (Blueprint $table) {
            $table->unsignedBigInteger('authorized_by')->nullable();
            $table->timestamp('authorized_at')->nullable();
            $table->string('authorization_note', 500)->nullable();
        });

        // Check constraint pour simuler ENUM dans Oracle
        DB::statement("
            ALTER TABLE messages_swift
            ADD CONSTRAINT status_check
            CHECK (status IN ('pending','processed','authorized','suspended','rejected','cancelled'))
        ");
    }

    public function down(): void
    {
        Schema::table('messages_swift', function (Blueprint $table) {
            $table->dropColumn(['authorized_by', 'authorized_at', 'authorization_note']);
        });

        DB::statement("
            ALTER TABLE messages_swift
            DROP CONSTRAINT status_check
        ");
    }
};