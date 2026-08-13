<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_payment_settings', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('provider', 20)->default('asaas');
            $table->string('environment', 20)->default('sandbox');
            $table->text('api_key')->nullable();
            $table->string('api_key_hint', 20)->nullable();
            $table->string('account_status', 40)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->boolean('online_payments_enabled')->default(false);
            $table->boolean('subscriptions_enabled')->default(false);
            $table->string('webhook_id')->nullable();
            $table->text('webhook_token')->nullable();
            $table->timestamp('webhook_registered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('service_subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('ad_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('description', 500)->nullable();
            $table->decimal('price', 10, 2);
            $table->string('cycle', 20)->default('MONTHLY');
            $table->text('terms')->nullable();
            $table->boolean('active')->default(false);
            $table->timestamps();
            $table->index(['ad_id', 'active']);
        });

        Schema::create('service_subscription_plan_procedure', function (Blueprint $table) {
            $table->foreignId('service_subscription_plan_id')->constrained('service_subscription_plans')->cascadeOnDelete();
            $table->foreignId('service_procedure_id')->constrained('service_procedures')->cascadeOnDelete();
            $table->unsignedSmallInteger('included_uses')->nullable();
            $table->primary(
                ['service_subscription_plan_id', 'service_procedure_id'],
                'subscription_plan_procedure_primary'
            );
        });

        Schema::create('service_client_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('service_subscription_plan_id')->constrained('service_subscription_plans')->restrictOnDelete();
            $table->foreignId('ad_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_setting_id')->constrained('service_payment_settings')->restrictOnDelete();
            $table->foreignId('customer_user_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 30)->default('creating');
            $table->string('billing_type', 20)->nullable();
            $table->string('asaas_customer_id')->nullable();
            $table->string('asaas_subscription_id')->nullable();
            $table->date('current_period_start')->nullable();
            $table->date('current_period_end')->nullable();
            $table->date('paid_through')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->index(['customer_user_id', 'status']);
            $table->index(['payment_setting_id', 'asaas_subscription_id'], 'client_subscriptions_asaas_lookup');
        });

        Schema::create('service_subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_setting_id')->constrained('service_payment_settings')->cascadeOnDelete();
            $table->foreignId('service_client_subscription_id')->constrained('service_client_subscriptions')->cascadeOnDelete();
            $table->string('asaas_payment_id');
            $table->string('status', 30)->default('pending');
            $table->string('billing_type', 20)->nullable();
            $table->decimal('value', 10, 2);
            $table->decimal('net_value', 10, 2)->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('invoice_url')->nullable();
            $table->timestamps();
            $table->unique(['payment_setting_id', 'asaas_payment_id'], 'subscription_payments_asaas_unique');
            $table->index(['service_client_subscription_id', 'status'], 'subscription_payments_status');
        });

        Schema::create('service_payment_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_setting_id')->constrained('service_payment_settings')->cascadeOnDelete();
            $table->string('event_id');
            $table->string('event_type', 80);
            $table->string('resource_id')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['payment_setting_id', 'event_id'], 'payment_webhook_events_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_payment_webhook_events');
        Schema::dropIfExists('service_subscription_payments');
        Schema::dropIfExists('service_client_subscriptions');
        Schema::dropIfExists('service_subscription_plan_procedure');
        Schema::dropIfExists('service_subscription_plans');
        Schema::dropIfExists('service_payment_settings');
    }
};
