<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('swift_message_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_id');
            $table->string('tag_name', 10);        // ex: '20', '32A', '71A'
            $table->text('tag_value');              // CLOB en Oracle
            $table->timestamps();

            $table->foreign('message_id')
                  ->references('id')
                  ->on('messages_swift')
                  ->onDelete('cascade');

            $table->index(['message_id', 'tag_name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('swift_message_details');
    }
};