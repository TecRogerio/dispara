<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('chat_id')->index();
            $table->unsignedBigInteger('contact_id')->nullable()->index();

            // id único do provedor (evolution/whatsapp)
            $table->string('provider_message_id', 190)->nullable()->index();

            // inbound | outbound
            $table->string('direction', 12)->index();

            // text | image | audio | video | document | unknown...
            $table->string('type', 30)->default('text');

            $table->longText('body')->nullable();

            // received | sent | failed | pending...
            $table->string('status', 20)->default('received');

            // timestamp do evento no WhatsApp (quando existir)
            $table->timestamp('message_at')->nullable()->index();

            // payload completo (debug/auditoria)
            $table->json('raw')->nullable();

            $table->timestamps();

            $table->foreign('chat_id')->references('id')->on('chats')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
