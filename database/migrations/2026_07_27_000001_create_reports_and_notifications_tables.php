<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->string('publication_ip', 45)->nullable()->after('views');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('suspended_at')->nullable()->after('role');
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('ad_id')->nullable()->constrained('ads')->nullOnDelete();
            $table->foreignId('advertiser_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reporter_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subject_type', 20);
            $table->string('ad_title_snapshot');
            $table->string('ad_module_snapshot', 30);
            $table->string('reason', 50);
            $table->string('severity', 20);
            $table->text('details')->nullable();
            $table->json('evidence_paths')->nullable();
            $table->boolean('wants_notification')->default(false);
            $table->string('status', 20)->default('open');
            $table->string('admin_action', 40)->nullable();
            $table->text('resolution_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'severity']);
        });

        Schema::create('report_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('report_id')->nullable()->constrained('reports')->nullOnDelete();
            $table->string('kind', 30)->default('report_update');
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_notifications');
        Schema::dropIfExists('reports');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('suspended_at');
        });

        Schema::table('ads', function (Blueprint $table) {
            $table->dropColumn('publication_ip');
        });
    }
};
