<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhatsappMessagesTable extends Migration
{
    public function up()
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->bigIncrements('id');

            // empresa (user)
            $table->unsignedBigInteger('user_id');

            // instância usada
            $table->unsignedBigInteger('whatsapp_instance_id');

            // dados do envio
            $table->string('to', 30); // somente dígitos (ex: 5599999999999)
            $table->text('message');

            // status e retorno
            $table->string('status', 30)->default('queued'); // queued/sent/failed
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->longText('response_json')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'whatsapp_instance_id']);
            $table->index(['user_id', 'status']);

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('whatsapp_instance_id')
                ->references('id')->on('whatsapp_instances')
                ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            try { $table->dropForeign(['user_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['whatsapp_instance_id']); } catch (\Throwable $e) {}
        });

        Schema::dropIfExists('whatsapp_messages');
    }
}
