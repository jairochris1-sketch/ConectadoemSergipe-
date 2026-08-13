<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_help_responses', function (Blueprint $table) {
            $table->string('status', 20)->default('published')->index()->after('message');
            $table->string('moderation_reason', 500)->nullable()->after('is_selected');
            $table->foreignId('reviewed_by')->nullable()->after('moderation_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
        });

        Schema::create('community_help_response_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_help_response_id')
                ->constrained('community_help_responses')
                ->cascadeOnDelete();
            $table->foreignId('reporter_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason', 30);
            $table->string('details', 700)->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['community_help_response_id', 'reporter_user_id'],
                'community_help_response_report_user_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_help_response_reports');

        Schema::table('community_help_responses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['status', 'moderation_reason', 'reviewed_at']);
        });
    }
};
