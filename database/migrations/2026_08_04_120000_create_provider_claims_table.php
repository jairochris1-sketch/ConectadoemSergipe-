<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->boolean('is_claimed')->default(true)->after('publication_ip');
            $table->timestamp('claimed_at')->nullable()->after('is_claimed');
            $table->string('contact_phone', 30)->nullable()->after('claimed_at');
            $table->string('contact_whatsapp', 30)->nullable()->after('contact_phone');
        });

        Schema::create('provider_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_id')->constrained('ads')->cascadeOnDelete();
            $table->foreignId('claimant_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('relationship', 30);
            $table->string('verification_phone', 30)->nullable();
            $table->string('verification_email')->nullable();
            $table->text('explanation')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['ad_id', 'status']);
            $table->index(['claimant_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_claims');

        Schema::table('ads', function (Blueprint $table) {
            $table->dropColumn([
                'is_claimed',
                'claimed_at',
                'contact_phone',
                'contact_whatsapp',
            ]);
        });
    }
};
