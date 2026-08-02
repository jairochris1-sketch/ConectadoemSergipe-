<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabela de planos (grupos de acesso)
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();        // 'free', 'pro', 'enterprise'
            $table->string('name');                  // 'Gratuito', 'Plano PRO', 'Plano Premium'
            $table->string('badge_label')->nullable(); // 'MAIS ACESSADO', 'MELHOR ESCOLHA'
            $table->text('headline')->nullable();    // frase de marketing
            $table->text('description')->nullable(); // subtítulo
            $table->decimal('price', 10, 2)->default(0); // 0, 99.90, 499.00
            $table->string('color')->default('secondary'); // 'secondary', 'primary', 'purple'
            $table->boolean('is_active')->default(true);
            $table->boolean('is_highlighted')->default(false); // destaque na página
            $table->integer('sort_order')->default(99);
            $table->timestamps();
        });

        // Tabela de features (serviços disponíveis na plataforma)
        Schema::create('plan_features', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();   // 'store_limit', 'product_limit' etc.
            $table->string('name');            // 'Lojas simultâneas', 'Produtos por loja'
            $table->string('description')->nullable(); // descrição interna
            $table->enum('type', ['boolean', 'integer', 'unlimited'])->default('integer');
            $table->integer('sort_order')->default(99);
            $table->timestamps();
        });

        // Tabela pivot: qual plano tem qual feature com qual valor
        Schema::create('plan_feature_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->foreignId('plan_feature_id')->constrained('plan_features')->cascadeOnDelete();
            $table->string('value')->nullable(); // null = ilimitado, '0' = bloqueado, '500' = numérico
            $table->boolean('show_on_page')->default(true); // exibir na página pública de planos
            $table->timestamps();

            $table->unique(['plan_id', 'plan_feature_id']);
        });

        // Override individual por usuário (futuro: liberar feature extra fora do plano)
        Schema::create('user_feature_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('plan_feature_id')->constrained('plan_features')->cascadeOnDelete();
            $table->string('value')->nullable(); // null = ilimitado
            $table->string('reason')->nullable(); // motivo do override (para auditoria)
            $table->timestamps();

            $table->unique(['user_id', 'plan_feature_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_feature_overrides');
        Schema::dropIfExists('plan_feature_values');
        Schema::dropIfExists('plan_features');
        Schema::dropIfExists('plans');
    }
};
