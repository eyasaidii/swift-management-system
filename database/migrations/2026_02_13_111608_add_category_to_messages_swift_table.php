<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages_swift', function (Blueprint $table) {
            $table->string('CATEGORY', 2)->nullable()->after('TYPE_MESSAGE');
        });
    }

    public function down(): void
    {
        Schema::table('messages_swift', function (Blueprint $table) {
            $table->dropColumn('CATEGORY');
        });
    }
};