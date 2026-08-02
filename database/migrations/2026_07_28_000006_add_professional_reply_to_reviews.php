<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->text('professional_reply')->nullable()->after('comment');
            $table->foreignId('professional_reply_user_id')
                ->nullable()
                ->after('professional_reply')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('professional_replied_at')->nullable()->after('professional_reply_user_id');
            $table->timestamp('professional_reply_edited_at')->nullable()->after('professional_replied_at');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropConstrainedForeignId('professional_reply_user_id');
            $table->dropColumn([
                'professional_reply',
                'professional_replied_at',
                'professional_reply_edited_at',
            ]);
        });
    }
};
