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
            
            // ✅ VÉRIFIER ET AJOUTER LES COLONNES MANQUANTES
            if (!Schema::hasColumn('messages_swift', 'direction')) {
                $table->enum('direction', ['IN', 'OUT'])->default('IN')->after('xml_brut');
            }
            
            if (!Schema::hasColumn('messages_swift', 'sender_bic')) {
                $table->string('sender_bic', 11)->nullable()->after('direction');
            }
            
            if (!Schema::hasColumn('messages_swift', 'receiver_bic')) {
                $table->string('receiver_bic', 11)->nullable()->after('sender_bic');
            }
            
            if (!Schema::hasColumn('messages_swift', 'sender_account')) {
                $table->string('sender_account', 34)->nullable()->after('receiver_bic');
            }
            
            if (!Schema::hasColumn('messages_swift', 'receiver_account')) {
                $table->string('receiver_account', 34)->nullable()->after('sender_account');
            }
            
            if (!Schema::hasColumn('messages_swift', 'sender_name')) {
                $table->string('sender_name')->nullable()->after('receiver_account');
            }
            
            if (!Schema::hasColumn('messages_swift', 'receiver_name')) {
                $table->string('receiver_name')->nullable()->after('sender_name');
            }
            
            if (!Schema::hasColumn('messages_swift', 'amount')) {
                $table->decimal('amount', 18, 2)->default(0)->after('receiver_name');
            }
            
            if (!Schema::hasColumn('messages_swift', 'currency')) {
                $table->string('currency', 3)->default('EUR')->after('amount');
            }
            
            if (!Schema::hasColumn('messages_swift', 'value_date')) {
                $table->date('value_date')->nullable()->after('currency');
            }
            
            if (!Schema::hasColumn('messages_swift', 'description')) {
                $table->text('description')->nullable()->after('value_date');
            }
            
            if (!Schema::hasColumn('messages_swift', 'status')) {
                $table->string('status', 20)->default('pending')->after('description');
            }
            
            if (!Schema::hasColumn('messages_swift', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('status');
                $table->foreign('created_by')->references('id')->on('users');
            }
            
            if (!Schema::hasColumn('messages_swift', 'processed_at')) {
                $table->timestamp('processed_at')->nullable()->after('created_by');
            }
            
            if (!Schema::hasColumn('messages_swift', 'metadata')) {
                $table->text('metadata')->nullable()->after('processed_at');
            }
        });

        // ✅ AJOUT DES INDEX - VERSION ORACLE
        try {
            DB::statement('CREATE INDEX idx_ms_type_dir ON messages_swift (type_message, direction)');
        } catch (\Exception $e) {
            // Index existe déjà
        }

        try {
            DB::statement('CREATE INDEX idx_ms_bic ON messages_swift (sender_bic, receiver_bic)');
        } catch (\Exception $e) {
            // Index existe déjà
        }

        try {
            DB::statement('CREATE INDEX idx_ms_reference ON messages_swift (reference)');
        } catch (\Exception $e) {
            // Index existe déjà
        }

        try {
            DB::statement('CREATE INDEX idx_ms_status ON messages_swift (status)');
        } catch (\Exception $e) {
            // Index existe déjà
        }

        try {
            DB::statement('CREATE INDEX idx_ms_created_by ON messages_swift (created_by)');
        } catch (\Exception $e) {
            // Index existe déjà
        }

        try {
            // ✅ NE PAS CRÉER D'INDEX SUR created_at si la colonne n'existe pas
            // Vérifier d'abord si la colonne existe
            $hasCreatedAt = DB::select("SELECT COLUMN_NAME FROM ALL_TAB_COLUMNS WHERE TABLE_NAME = 'MESSAGES_SWIFT' AND COLUMN_NAME = 'CREATED_AT'");
            if (!empty($hasCreatedAt)) {
                DB::statement('CREATE INDEX idx_ms_created_at ON messages_swift (created_at)');
            }
        } catch (\Exception $e) {
            // Ignorer
        }

        try {
            DB::statement('CREATE INDEX idx_ms_value_date ON messages_swift (value_date)');
        } catch (\Exception $e) {
            // Index existe déjà
        }

        // ✅ AJOUT DES CONTRAINTES CHECK
        try {
            DB::statement("ALTER TABLE messages_swift ADD CONSTRAINT chk_ms_direction CHECK (direction IN ('IN', 'OUT'))");
        } catch (\Exception $e) {
            // Contrainte existe déjà
        }

        try {
            DB::statement("ALTER TABLE messages_swift ADD CONSTRAINT chk_ms_status CHECK (status IN ('pending', 'processed', 'rejected', 'cancelled'))");
        } catch (\Exception $e) {
            // Contrainte existe déjà
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages_swift', function (Blueprint $table) {
            
            // SUPPRIMER LES CONTRAINTES
            try {
                DB::statement('ALTER TABLE messages_swift DROP CONSTRAINT chk_ms_direction');
            } catch (\Exception $e) {}

            try {
                DB::statement('ALTER TABLE messages_swift DROP CONSTRAINT chk_ms_status');
            } catch (\Exception $e) {}

            try {
                DB::statement('ALTER TABLE messages_swift DROP CONSTRAINT fk_messages_swift_created_by');
            } catch (\Exception $e) {}
            
            // SUPPRIMER LES INDEX
            $indexes = [
                'idx_ms_type_dir',
                'idx_ms_bic',
                'idx_ms_reference',
                'idx_ms_status',
                'idx_ms_created_by',
                'idx_ms_created_at',
                'idx_ms_value_date'
            ];

            foreach ($indexes as $index) {
                try {
                    DB::statement("DROP INDEX $index");
                } catch (\Exception $e) {}
            }
            
            // SUPPRIMER LES COLONNES AJOUTÉES
            $columns = [
                'direction',
                'sender_bic',
                'receiver_bic',
                'sender_account',
                'receiver_account',
                'sender_name',
                'receiver_name',
                'amount',
                'currency',
                'value_date',
                'description',
                'status',
                'created_by',
                'processed_at',
                'metadata'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('messages_swift', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};