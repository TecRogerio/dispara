<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->string('slug', 160)->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tenant padrão (id=1) para não quebrar o sistema agora
        DB::table('tenants')->insert([
            'id' => 1,
            'name' => 'Empresa Padrão',
            'slug' => 'default',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
