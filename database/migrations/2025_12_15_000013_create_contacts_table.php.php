<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ Se a tabela já existe (banco “sujo” / import / phpMyAdmin), não quebra o migrate
        if (Schema::hasTable('contacts')) {
            return;
        }

        Schema::create('contacts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('tenant_id')->default(1);

            $table->string('name', 160)->nullable();
            $table->string('pushname', 160)->nullable();

            // E.164 sem + (ex: 5547999999999) — equivalente ao teu phone_digits
            $table->string('phone_e164', 20);

            $table->string('phone_raw', 80)->nullable();

            $table->string('profile_pic_url', 255)->nullable();
            $table->string('email', 160)->nullable();

            $table->boolean('is_group')->default(false);

            $table->json('metadata')->nullable();

            $table->timestamps();

            // Índices/unique
            $table->unique(['tenant_id', 'phone_e164'], 'uq_contacts_tenant_phone');
            $table->index(['tenant_id', 'name'], 'idx_contacts_tenant_name');
            $table->index(['tenant_id', 'is_group'], 'idx_contacts_tenant_is_group');

            // FK
            $table->foreign('tenant_id', 'fk_contacts_tenant')
                ->references('id')->on('tenants')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
