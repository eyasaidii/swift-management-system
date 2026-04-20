<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Exécuter cette migration uniquement pour Oracle (éviter les requêtes spécifiques à Oracle sur SQLite/MySQL)
        $driver = config('database.default');
        if ($driver === 'oracle' || str_contains($driver, 'oci') || str_contains($driver, 'oracle')) {
            // Rendre XML_BRUT nullable si nécessaire
            if (! $this->isNullable('XML_BRUT')) {
                DB::statement('ALTER TABLE messages_swift MODIFY (XML_BRUT NULL)');
            }

            // Rendre MT_CONTENT nullable si nécessaire
            if (! $this->isNullable('MT_CONTENT')) {
                DB::statement('ALTER TABLE messages_swift MODIFY (MT_CONTENT NULL)');
            }
        } else {
            // Pour d'autres drivers (SQLite pendant le dev local), ignorer car opération non nécessaire
            return;
        }
    }

    /**
     * Vérifie si une colonne est déjà nullable.
     */
    private function isNullable(string $column): bool
    {
        $result = DB::selectOne("
            SELECT NULLABLE 
            FROM USER_TAB_COLUMNS 
            WHERE TABLE_NAME = 'MESSAGES_SWIFT' 
              AND COLUMN_NAME = ?
        ", [strtoupper($column)]);

        return $result && $result->nullable === 'Y';
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionnel : remettre les colonnes NOT NULL (nécessite qu'aucune valeur NULL ne soit présente)
        // $this->makeNotNullIfNeeded('XML_BRUT');
        // $this->makeNotNullIfNeeded('MT_CONTENT');
    }
};
