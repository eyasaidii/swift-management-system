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
        Schema::create('regles_aml', function (Blueprint $table) {
            $table->id();
            $table->string('nom_regle');
            $table->decimal('seuil', 15, 2);
            $table->string('pays');
            $table->boolean('active');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regle_amls');
    }
};
