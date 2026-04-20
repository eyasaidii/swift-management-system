<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter les colonnes seulement si elles n'existent pas (idempotence)
        if (! Schema::hasColumn('messages_swift', 'authorized_by') || ! Schema::hasColumn('messages_swift', 'authorized_at') || ! Schema::hasColumn('messages_swift', 'authorization_note')) {
            Schema::table('messages_swift', function (Blueprint $table) {
                if (! Schema::hasColumn('messages_swift', 'authorized_by')) {
                    $table->unsignedBigInteger('authorized_by')->nullable();
                }
                if (! Schema::hasColumn('messages_swift', 'authorized_at')) {
                    $table->timestamp('authorized_at')->nullable();
                }
                if (! Schema::hasColumn('messages_swift', 'authorization_note')) {
                    $table->string('authorization_note', 500)->nullable();
                }
            });
        }

        // Check constraint pour simuler ENUM dans Oracle — ignorer sur SQLite/local
        $driver = config('database.default');
        if ($driver === 'oracle' || str_contains($driver, 'oci') || str_contains($driver, 'oracle')) {
            DB::statement("ALTER TABLE messages_swift ADD CONSTRAINT status_check CHECK (status IN ('pending','processed','authorized','suspended','rejected','cancelled'))");
        }
    }

    public function down(): void
    {
        Schema::table('messages_swift', function (Blueprint $table) {
            if (Schema::hasColumn('messages_swift', 'authorized_by')) {
                $table->dropColumn('authorized_by');
            }
            if (Schema::hasColumn('messages_swift', 'authorized_at')) {
                $table->dropColumn('authorized_at');
            }
            if (Schema::hasColumn('messages_swift', 'authorization_note')) {
                $table->dropColumn('authorization_note');
            }
        });

        $driver = config('database.default');
        if ($driver === 'oracle' || str_contains($driver, 'oci') || str_contains($driver, 'oracle')) {
            DB::statement('ALTER TABLE messages_swift DROP CONSTRAINT status_check');
        }
    }
};
