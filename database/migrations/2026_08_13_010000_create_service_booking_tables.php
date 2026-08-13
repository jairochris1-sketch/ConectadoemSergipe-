<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->boolean('booking_enabled')->default(false)->after('service_modes');
        });

        Schema::create('service_procedures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->decimal('price', 10, 2);
            $table->unsignedSmallInteger('duration_minutes');
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->index(['ad_id', 'active']);
        });

        Schema::create('service_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->index(['ad_id', 'active']);
        });

        Schema::create('service_staff_procedure', function (Blueprint $table) {
            $table->foreignId('service_staff_id')->constrained('service_staff')->cascadeOnDelete();
            $table->foreignId('service_procedure_id')->constrained('service_procedures')->cascadeOnDelete();
            $table->primary(['service_staff_id', 'service_procedure_id'], 'staff_procedure_primary');
        });

        Schema::create('service_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_staff_id')->constrained('service_staff')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->timestamps();
            $table->unique(['service_staff_id', 'day_of_week']);
        });

        Schema::create('service_appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_procedure_id')->constrained('service_procedures')->restrictOnDelete();
            $table->foreignId('service_staff_id')->constrained('service_staff')->restrictOnDelete();
            $table->foreignId('customer_user_id')->constrained('users')->restrictOnDelete();
            $table->string('customer_name');
            $table->string('customer_phone', 20)->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->decimal('service_price', 10, 2);
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['service_staff_id', 'starts_at', 'ends_at'], 'appointments_staff_period');
            $table->index(['ad_id', 'status', 'starts_at']);
        });

        Schema::create('service_financial_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->string('category', 60);
            $table->string('description', 180);
            $table->decimal('amount', 10, 2);
            $table->date('occurred_on');
            $table->timestamps();
            $table->index(['ad_id', 'occurred_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_financial_entries');
        Schema::dropIfExists('service_appointments');
        Schema::dropIfExists('service_availabilities');
        Schema::dropIfExists('service_staff_procedure');
        Schema::dropIfExists('service_staff');
        Schema::dropIfExists('service_procedures');
        Schema::table('ads', fn (Blueprint $table) => $table->dropColumn('booking_enabled'));
    }
};
