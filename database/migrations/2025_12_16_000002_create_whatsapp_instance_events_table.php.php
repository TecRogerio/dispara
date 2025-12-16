<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('whatsapp_instance_events')) return;

        Schema::create('whatsapp_instance_events', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('tenant_id')->default(1);
            $table->unsignedBigInteger('whatsapp_instance_id');

            // CONNECTED, DISCONNECTED, QRCODE, ERROR, SEND_OK, SEND_FAIL, etc.
            $table->string('event', 40);

            // opcional: "evolution", "system", "webhook"
            $table->string('source', 30)->nullable();

            // infos úteis pra depurar
            $table->string('status', 40)->nullable();     // ex: CONNECTED
            $table->string('phone', 40)->nullable();      // ex: +55...
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 190)->nullable();

            $table->json('payload')->nullable();          // resposta/erro bruto
            $table->text('message')->nullable();          // texto curto (erro resumido)

            $table->timestamps();

            $table->index(['tenant_id', 'whatsapp_instance_id', 'created_at'], 'idx_evt_tenant_instance_dt');
            $table->index(['tenant_id', 'event', 'created_at'], 'idx_evt_tenant_event_dt');

            $table->foreign('whatsapp_instance_id')
                ->references('id')->on('whatsapp_instances')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_instance_events');
    }
};
