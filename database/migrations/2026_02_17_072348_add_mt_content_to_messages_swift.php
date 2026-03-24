<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages_swift', function (Blueprint $table) {
            // ✅ Ajouter les nouvelles colonnes
            $table->string('CATEGORIE', 10)->nullable()->after('TYPE_MESSAGE');
            $table->longText('MT_CONTENT')->nullable()->after('XML_BRUT');
            $table->json('TRANSLATION_ERRORS')->nullable()->after('METADATA');
            
            // Index pour améliorer les performances
            $table->index('CATEGORIE');
        });
    }

    public function down(): void
    {
        Schema::table('messages_swift', function (Blueprint $table) {
            $table->dropColumn(['CATEGORIE', 'MT_CONTENT', 'TRANSLATION_ERRORS']);
            $table->dropIndex(['CATEGORIE']);
        });
    }
};