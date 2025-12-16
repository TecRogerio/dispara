<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'tenant_id')) {
                $table->unsignedBigInteger('tenant_id')->default(1)->after('id');
                $table->index('tenant_id', 'idx_users_tenant');
            }
        });

        // Backfill: garante que todo mundo tenha tenant_id
        DB::table('users')->whereNull('tenant_id')->update(['tenant_id' => 1]);

        // FK (se o banco permitir no seu ambiente)
        Schema::table('users', function (Blueprint $table) {
            // evita erro se já existir
            try {
                $table->foreign('tenant_id', 'fk_users_tenant')
                    ->references('id')->on('tenants')
                    ->onDelete('restrict')
                    ->onUpdate('cascade');
            } catch (\Throwable $e) {
                // ignora em ambientes que não permitem alterar FK facilmente
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            try { $table->dropForeign('fk_users_tenant'); } catch (\Throwable $e) {}
            try { $table->dropIndex('idx_users_tenant'); } catch (\Throwable $e) {}
            if (Schema::hasColumn('users', 'tenant_id')) {
                $table->dropColumn('tenant_id');
            }
        });
    }
};
