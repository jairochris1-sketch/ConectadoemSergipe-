<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_appointments', function (Blueprint $table) {
            $table->foreignId('customer_user_id')->nullable()->change();
        });

        Schema::create('service_schedule_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_staff_id')->nullable()->constrained('service_staff')->cascadeOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('reason', 180)->nullable();
            $table->timestamps();
            $table->index(['ad_id', 'starts_at', 'ends_at'], 'schedule_blocks_ad_period');
            $table->index(['service_staff_id', 'starts_at', 'ends_at'], 'schedule_blocks_staff_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_schedule_blocks');

        DB::table('service_appointments')->whereNull('customer_user_id')->delete();

        Schema::table('service_appointments', function (Blueprint $table) {
            $table->foreignId('customer_user_id')->nullable(false)->change();
        });
    }
};
