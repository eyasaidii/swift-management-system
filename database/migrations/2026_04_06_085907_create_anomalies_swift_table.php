<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anomalies_swift', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')
                ->constrained('messages_swift')
                ->onDelete('cascade');
            $table->decimal('score', 5, 2)->nullable();
            $table->string('niveau_risque', 10)->nullable();
            $table->json('raisons')->nullable();
            $table->foreignId('verifie_par')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamp('verifie_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anomalies_swift');
    }
};
