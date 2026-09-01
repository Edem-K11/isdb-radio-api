<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stream_settings', function (Blueprint $table) {
            $table->id();
            $table->string('station_name')->default('Radio ISDB');
            $table->string('slogan')->nullable();
            $table->string('stream_url');
            $table->string('backup_url')->nullable();
            $table->string('codec', 8)->default('mp3'); // mp3 | aac
            $table->boolean('is_on_air')->default(true);
            $table->string('offline_message')->default(
                "La radio est actuellement hors antenne. Revenez bientot !"
            );
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stream_settings');
    }
};
