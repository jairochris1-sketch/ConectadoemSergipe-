<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('culture_works', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('category')->default('cordel'); // cordel, literatura, musica, arte_visual
            $table->string('theme')->nullable(); // sertao, cultura_local, amor, humor, historia, etc.
            $table->string('external_url')->nullable(); // Link p/ Amazon, Hotmart, etc.
            $table->text('embed_media_url')->nullable(); // URL / iframe do YouTube ou Spotify
            $table->string('status')->default('published'); // draft, published
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('ad_id')->nullable()->constrained('ads')->onDelete('set null'); // Produto vinculado para pedido
            $table->unsignedBigInteger('views_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('culture_works');
    }
};
