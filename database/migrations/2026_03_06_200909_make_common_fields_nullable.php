<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $columns = [
            'REFERENCE', 'AMOUNT', 'CURRENCY', 'VALUE_DATE', 'DESCRIPTION',
            'SENDER_BIC', 'RECEIVER_BIC', 'SENDER_ACCOUNT', 'RECEIVER_ACCOUNT',
            'SENDER_NAME', 'RECEIVER_NAME'
        ];

        foreach ($columns as $column) {
            // Vérifier si la colonne est actuellement NOT NULL
            $result = DB::selectOne("
                SELECT NULLABLE 
                FROM USER_TAB_COLUMNS 
                WHERE TABLE_NAME = 'MESSAGES_SWIFT' 
                  AND COLUMN_NAME = ?
            ", [$column]);

            if ($result && $result->nullable === 'N') {
                DB::statement("ALTER TABLE messages_swift MODIFY ($column NULL)");
            }
        }
    }

    public function down()
    {
        // Remettre en NOT NULL nécessiterait de s'assurer qu'aucune ligne n'a NULL.
        // On laisse vide pour éviter les erreurs.
    }
};