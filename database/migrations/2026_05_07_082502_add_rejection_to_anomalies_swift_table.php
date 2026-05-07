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
        Schema::table('anomalies_swift', function (Blueprint $table) {
            $table->unsignedBigInteger('rejetee_par')->nullable()->after('verifie_at');
            $table->timestamp('rejetee_at')->nullable()->after('rejetee_par');
            $table->foreign('rejetee_par')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anomalies_swift', function (Blueprint $table) {
            $table->dropForeign(['rejetee_par']);
            $table->dropColumn(['rejetee_par', 'rejetee_at']);
        });
    }
};
