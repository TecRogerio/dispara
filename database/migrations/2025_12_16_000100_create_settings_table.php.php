<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('settings')) {
            return;
        }

        Schema::create('settings', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Multiempresa (segue o padrão do teu banco: tenant_id NOT NULL default 1)
            $table->unsignedBigInteger('tenant_id')->default(1);

            // Ex: campaign.pause_every, campaign.pause_seconds, campaign.limit_max
            $table->string('key', 120);

            // Guardamos como string (json/number/bool vão como texto; model/helper converte depois)
            $table->text('value')->nullable();

            $table->timestamps();

            // Índices / unicidade
            $table->unique(['tenant_id', 'key'], 'uq_settings_tenant_key');
            $table->index(['tenant_id', 'key'], 'idx_settings_tenant_key');

            // FK (se existir tenants)
            if (Schema::hasTable('tenants')) {
                $table->foreign('tenant_id')
                    ->references('id')->on('tenants')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            try { $table->dropForeign(['tenant_id']); } catch (\Throwable $e) {}
            try { $table->dropUnique('uq_settings_tenant_key'); } catch (\Throwable $e) {}
            try { $table->dropIndex('idx_settings_tenant_key'); } catch (\Throwable $e) {}
        });

        Schema::dropIfExists('settings');
    }
};
