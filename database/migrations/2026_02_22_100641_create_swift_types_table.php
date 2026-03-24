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
        Schema::create('swift_types', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('type'); // SWIFT type (e.g., MT100, MT200, etc.)
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade'); // Foreign key to categories table
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('swift_types');
    }
};
