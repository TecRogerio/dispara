<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignRecipientsTable extends Migration
{
    public function up()
    {
        // ✅ Se a tabela já existe (banco importado), não tenta criar de novo
        if (Schema::hasTable('campaign_recipients')) {
            return;
        }

        Schema::create('campaign_recipients', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('campaign_id');

            // Dados importados
            $table->string('name', 160)->nullable();

            // Telefone normalizado (somente dígitos). Ex: 55DDDNÚMERO
            $table->string('phone_digits', 32);

            // Telefone original (como veio do csv/txt/cola)
            $table->string('phone_raw', 64)->nullable();

            // Validação local (regex/rules)
            $table->boolean('is_valid')->default(true);
            $table->string('invalid_reason', 190)->nullable();

            // Status de envio por destinatário
            $table->enum('send_status', ['pending','queued','sent','failed','delivered','read'])
                ->default('pending');

            // IDs/retornos da Evolution (dependem da build)
            $table->string('provider_message_id', 120)->nullable();

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();

            $table->text('last_error')->nullable();

            $table->timestamps();

            $table->index(['campaign_id', 'send_status']);
            $table->index(['campaign_id', 'is_valid']);

            // ✅ Dedup forte por campanha+número
            $table->unique(['campaign_id', 'phone_digits'], 'uniq_campaign_phone');

            // ✅ FK só se a tabela campaigns existir (evita erro por ordem desalinhada)
            if (Schema::hasTable('campaigns')) {
                $table->foreign('campaign_id')
                    ->references('id')->on('campaigns')
                    ->onDelete('cascade')->onUpdate('cascade');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('campaign_recipients')) {
            return;
        }

        Schema::table('campaign_recipients', function (Blueprint $table) {
            try { $table->dropForeign(['campaign_id']); } catch (\Throwable $e) {}
            try { $table->dropUnique('uniq_campaign_phone'); } catch (\Throwable $e) {}
        });

        Schema::dropIfExists('campaign_recipients');
    }
}
