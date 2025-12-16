<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhatsappInstancesTable extends Migration
{
    public function up()
    {
        // ✅ Se a tabela já existir (banco importado / migração já aplicada), não recria
        if (Schema::hasTable('whatsapp_instances')) {
            return;
        }

        Schema::create('whatsapp_instances', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Empresa (user)
            $table->unsignedBigInteger('user_id');

            // Apelido interno (ex: "WhatsApp Loja Centro")
            $table->string('label', 120)->nullable();

            // Nome/chave real da instância na Evolution (ex: "loja-centro")
            $table->string('instance_name', 120);

            // Token da instância
            $table->string('token', 255)->nullable();

            // Ativa/desativa
            $table->boolean('is_active')->default(true);

            // Limite diário por instância
            $table->unsignedInteger('daily_limit')->default(200);

            // Contador simples do dia (V1)
            $table->unsignedInteger('sent_today')->default(0);
            $table->date('sent_today_date')->nullable();

            $table->timestamps();

            // Índices
            $table->index('user_id');
            $table->index(['user_id', 'is_active']);

            // Evita duplicar o mesmo instance_name dentro da mesma empresa
            $table->unique(['user_id', 'instance_name'], 'uniq_user_instance_name');

            // FK (users deve existir)
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down()
    {
        // ✅ Se a tabela não existir, não faz nada
        if (!Schema::hasTable('whatsapp_instances')) {
            return;
        }

        Schema::table('whatsapp_instances', function (Blueprint $table) {
            // Esses try/catch evitam erro se o banco não tiver exatamente esses nomes
            try { $table->dropForeign(['user_id']); } catch (\Throwable $e) {}
            try { $table->dropUnique('uniq_user_instance_name'); } catch (\Throwable $e) {}
        });

        Schema::dropIfExists('whatsapp_instances');
    }
}
