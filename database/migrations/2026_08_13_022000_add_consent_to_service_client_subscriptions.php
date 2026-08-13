<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_client_subscriptions', function (Blueprint $table) {
            $table->text('terms_snapshot')->nullable()->after('billing_type');
            $table->timestamp('consented_at')->nullable()->after('terms_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('service_client_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['terms_snapshot', 'consented_at']);
        });
    }
};
