<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_appointments', function (Blueprint $table) {
            $table->string('attendance_mode', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('service_appointments', function (Blueprint $table) {
            $table->dropColumn('attendance_mode');
        });
    }
};
