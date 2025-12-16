<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('campaign_recipients', function (Blueprint $table) {
            // Verificar se a coluna 'phone' existe antes de adicionar 'phone_normalized'
            if (Schema::hasColumn('campaign_recipients', 'phone')) {
                if (!Schema::hasColumn('campaign_recipients', 'phone_normalized')) {
                    $table->string('phone_normalized', 32)->nullable()->after('phone');
                }

                // Índice para busca/dedup
                $table->index(['campaign_id', 'phone_normalized'], 'idx_campaign_phone_norm');

                // ÚNICO por campanha (quando phone_normalized não for null)
                // MySQL: funciona como unique normal (se tiver null, permite múltiplos null)
                $table->unique(['campaign_id', 'phone_normalized'], 'uq_campaign_phone_norm');
            }
        });
    }

    public function down(): void
    {
        Schema::table('campaign_recipients', function (Blueprint $table) {
            // Verifique se a coluna e os índices existem antes de removê-los
            if (Schema::hasColumn('campaign_recipients', 'phone_normalized')) {
                $table->dropUnique('uq_campaign_phone_norm');
                $table->dropIndex('idx_campaign_phone_norm');
                $table->dropColumn('phone_normalized');
            }
        });
    }
};
