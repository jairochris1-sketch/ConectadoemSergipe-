<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('service_appointments', 'service_client_subscription_id')) {
            Schema::table('service_appointments', function (Blueprint $table) {
                $table->foreignId('service_client_subscription_id')
                    ->nullable()
                    ->after('customer_user_id')
                    ->constrained('service_client_subscriptions')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasTable('service_subscription_usages')) {
            Schema::create('service_subscription_usages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_client_subscription_id')->constrained('service_client_subscriptions')->cascadeOnDelete();
                $table->foreignId('service_appointment_id')->unique()->constrained('service_appointments')->cascadeOnDelete();
                $table->foreignId('service_procedure_id')->constrained('service_procedures')->restrictOnDelete();
                $table->date('cycle_start');
                $table->date('cycle_end');
                $table->unsignedSmallInteger('units')->default(1);
                $table->string('status', 20)->default('reserved');
                $table->timestamps();
                $table->index(['service_client_subscription_id', 'cycle_start', 'cycle_end'], 'subscription_usage_cycle');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_subscription_usages');
        if (Schema::hasColumn('service_appointments', 'service_client_subscription_id')) {
            Schema::table('service_appointments', function (Blueprint $table) {
                $table->dropConstrainedForeignId('service_client_subscription_id');
            });
        }
    }
};
