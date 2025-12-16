<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignMessagesAndAttachmentsTables extends Migration
{
    public function up()
    {
        // ---------------------------------------------------------
        // campaign_messages
        // ---------------------------------------------------------
        if (!Schema::hasTable('campaign_messages')) {
            Schema::create('campaign_messages', function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->unsignedBigInteger('campaign_id');

                // Texto (v1: texto simples; depois suportar variáveis {nome}, etc.)
                $table->longText('text')->nullable();

                // Tipo principal (para organizar)
                $table->enum('primary_type', ['text','media','document','audio','video','image','location','mixed'])
                    ->default('text');

                // Localização (se usada)
                $table->decimal('location_lat', 10, 7)->nullable();
                $table->decimal('location_lng', 10, 7)->nullable();
                $table->string('location_name', 160)->nullable();
                $table->string('location_address', 255)->nullable();

                $table->timestamps();

                // FK só se a tabela campaigns existir
                if (Schema::hasTable('campaigns')) {
                    $table->foreign('campaign_id')
                        ->references('id')->on('campaigns')
                        ->onDelete('cascade')->onUpdate('cascade');
                }
            });
        }

        // ---------------------------------------------------------
        // campaign_attachments
        // ---------------------------------------------------------
        if (!Schema::hasTable('campaign_attachments')) {
            Schema::create('campaign_attachments', function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->unsignedBigInteger('campaign_message_id');

                // Tipo do arquivo
                $table->enum('type', ['image','video','audio','document','sticker'])
                    ->default('document');

                // Nome original
                $table->string('original_name', 190)->nullable();

                // MIME (ex: image/png, application/pdf)
                $table->string('mime', 120)->nullable();

                // Caminho salvo no storage (ex: campaign/123/file.pdf)
                $table->string('path', 255);

                // Tamanho em bytes
                $table->unsignedBigInteger('size_bytes')->default(0);

                // Metadados opcionais (ex: duração áudio, dimensões imagem)
                $table->json('meta')->nullable();

                $table->timestamps();

                $table->index(['campaign_message_id', 'type']);

                // FK só se a tabela campaign_messages existir
                if (Schema::hasTable('campaign_messages')) {
                    $table->foreign('campaign_message_id')
                        ->references('id')->on('campaign_messages')
                        ->onDelete('cascade')->onUpdate('cascade');
                }
            });
        }
    }

    public function down()
    {
        // Remove FKs com segurança
        if (Schema::hasTable('campaign_attachments')) {
            Schema::table('campaign_attachments', function (Blueprint $table) {
                try { $table->dropForeign(['campaign_message_id']); } catch (\Throwable $e) {}
            });
        }

        if (Schema::hasTable('campaign_messages')) {
            Schema::table('campaign_messages', function (Blueprint $table) {
                try { $table->dropForeign(['campaign_id']); } catch (\Throwable $e) {}
            });
        }

        Schema::dropIfExists('campaign_attachments');
        Schema::dropIfExists('campaign_messages');
    }
}
