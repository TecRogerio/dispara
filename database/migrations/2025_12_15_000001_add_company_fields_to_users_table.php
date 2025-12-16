<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyFieldsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {

            // Nome da empresa (cada user = empresa)
            if (!Schema::hasColumn('users', 'company_name')) {
                $table->string('company_name', 150)->nullable()->after('name');
            }

            // Token da empresa (opcional - pode usar para integrar sua própria API)
            if (!Schema::hasColumn('users', 'api_token')) {
                $table->string('api_token', 80)->nullable()->unique()->after('password');
            }

            // Status da empresa (1=ativo, 0=inativo)
            if (!Schema::hasColumn('users', 'status')) {
                $table->tinyInteger('status')->default(1)->after('api_token');
            }

            // Limite diário padrão de disparos (pode ser usado como default geral)
            if (!Schema::hasColumn('users', 'daily_limit')) {
                $table->unsignedInteger('daily_limit')->default(200)->after('status');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {

            // Para dropar UNIQUE antes de dropar a coluna
            if (Schema::hasColumn('users', 'api_token')) {
                // nome do índice default: users_api_token_unique
                try { $table->dropUnique('users_api_token_unique'); } catch (\Throwable $e) {}
                $table->dropColumn('api_token');
            }

            if (Schema::hasColumn('users', 'company_name')) {
                $table->dropColumn('company_name');
            }

            if (Schema::hasColumn('users', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('users', 'daily_limit')) {
                $table->dropColumn('daily_limit');
            }
        });
    }
}
