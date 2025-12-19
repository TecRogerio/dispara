<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('whatsapp_instance_id')->index();

            // JID do contato/grupo no WhatsApp (ex: 5547999999999@s.whatsapp.net)
            $table->string('remote_jid', 120)->index();

            // Nome/label amigável (pode vir do contato)
            $table->string('title', 190)->nullable();

            $table->timestamp('last_message_at')->nullable();

            $table->timestamps();

            $table->unique(['whatsapp_instance_id', 'remote_jid'], 'uq_chat_instance_remote');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
