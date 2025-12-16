<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignMessagesTable extends Migration
{
    public function up()
    {
        // ✅ Se a tabela já existe (banco importado ou criada por outra migration), não tenta criar
        if (Schema::hasTable('campaign_messages')) {
            return;
        }

        Schema::create('campaign_messages', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('campaign_id');

            // Ordem da mensagem dentro da campanha
            $table->unsignedInteger('position')->default(1);

            // Tipo: text | image | video | audio | document | location | sticker...
            $table->string('type', 30)->default('text');

            // Conteúdo
            $table->longText('text')->nullable();
            $table->longText('caption')->nullable();

            // Localização (quando type = location)
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('location_name', 160)->nullable();
            $table->string('location_address', 200)->nullable();

            $table->timestamps();

            $table->index('campaign_id');
            $table->index(['campaign_id', 'type']);
            $table->unique(['campaign_id', 'position'], 'uniq_campaign_message_position');

            // ✅ FK só se a tabela campaigns existir
            if (Schema::hasTable('campaigns')) {
                $table->foreign('campaign_id')
                    ->references('id')->on('campaigns')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('campaign_messages')) {
            return;
        }

        Schema::table('campaign_messages', function (Blueprint $table) {
            try { $table->dropForeign(['campaign_id']); } catch (\Throwable $e) {}
            try { $table->dropUnique('uniq_campaign_message_position'); } catch (\Throwable $e) {}
        });

        Schema::dropIfExists('campaign_messages');
    }
}
