<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('campaign_messages', 'media_type')) {
                $table->string('media_type', 20)->nullable(); // image|video|document|audio
            }
            if (!Schema::hasColumn('campaign_messages', 'media_url')) {
                $table->text('media_url')->nullable(); // URL pública (ou base64 se quiser)
            }
            if (!Schema::hasColumn('campaign_messages', 'mime_type')) {
                $table->string('mime_type', 120)->nullable();
            }
            if (!Schema::hasColumn('campaign_messages', 'file_name')) {
                $table->string('file_name', 200)->nullable();
            }
            if (!Schema::hasColumn('campaign_messages', 'caption')) {
                $table->text('caption')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('campaign_messages', function (Blueprint $table) {
            foreach (['media_type','media_url','mime_type','file_name','caption'] as $col) {
                if (Schema::hasColumn('campaign_messages', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
