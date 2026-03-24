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
        Schema::create('messages_swift', function (Blueprint $table) {
            
            $table->id();
            $table->string('type_message'); // MT103, MT940, MT950, MT200, PACS.008, CAMT.053
            $table->string('reference')->unique(); // Référence unique
            $table->longText('xml_brut'); // Contenu XML brut
            $table->foreignId('import_job_id')->nullable(); // ID du job d'import
            
            // ✅ AJOUTER CES COLONNES MANQUANTES
            $table->enum('direction', ['IN', 'OUT'])->default('IN'); // Reçu ou Émis
            $table->string('sender_bic', 11)->nullable();
            $table->string('receiver_bic', 11)->nullable();
            $table->string('sender_account', 34)->nullable();
            $table->string('receiver_account', 34)->nullable();
            $table->string('sender_name')->nullable();
            $table->string('receiver_name')->nullable();
            $table->decimal('amount', 18, 2)->default(0);
            $table->string('currency', 3)->default('EUR');
            $table->date('value_date')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'processed', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamp('processed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // ✅ INDEX POUR PERFORMANCES
            $table->index(['type_message', 'direction']);
            $table->index(['sender_bic', 'receiver_bic']);
            $table->index('reference');
            $table->index('status');
            $table->index('created_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages_swift');
    }
};