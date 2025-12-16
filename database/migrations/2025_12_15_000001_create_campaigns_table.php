<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignsTable extends Migration
{
    public function up()
    {
        // ✅ Se a tabela já existe (banco importado), não tenta criar de novo
        if (Schema::hasTable('campaigns')) {
            return;
        }

        Schema::create('campaigns', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Empresa dona da campanha
            $table->unsignedBigInteger('user_id');

            // Qual instância (número) vai disparar
            $table->unsignedBigInteger('whatsapp_instance_id');

            $table->string('name', 160);

            // Config de envio
            $table->unsignedInteger('delay_min_seconds')->default(9);    // mínimo 9s
            $table->unsignedInteger('delay_max_seconds')->default(12);   // random entre min/max
            $table->unsignedInteger('burst_max')->default(20);           // ex: enviar 20
            $table->unsignedInteger('burst_pause_seconds')->default(30); // pausa maior 30s
            $table->unsignedInteger('daily_limit_override')->nullable(); // sobrescreve limite (opcional)

            // Status geral da campanha
            $table->enum('status', ['draft','validated','queued','running','paused','finished','canceled','failed'])
                ->default('draft');

            // Números processados
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('valid_recipients')->default(0);
            $table->unsignedInteger('invalid_recipients')->default(0);

            // Auditoria
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['whatsapp_instance_id', 'status']);

            // ✅ FK do usuário (users deve existir)
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade')->onUpdate('cascade');

            // ✅ FK da instância: só cria se a tabela alvo existir
            // (evita erro se a ordem das migrations estiver desalinhada)
            if (Schema::hasTable('whatsapp_instances')) {
                $table->foreign('whatsapp_instance_id')
                    ->references('id')->on('whatsapp_instances')
                    ->onDelete('restrict')->onUpdate('cascade');
            }
        });
    }

    public function down()
    {
        // Mantém seguro em ambientes diferentes
        if (!Schema::hasTable('campaigns')) {
            return;
        }

        Schema::table('campaigns', function (Blueprint $table) {
            try { $table->dropForeign(['user_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['whatsapp_instance_id']); } catch (\Throwable $e) {}
        });

        Schema::dropIfExists('campaigns');
    }
}
