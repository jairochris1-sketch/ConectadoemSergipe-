<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->boolean('featured')->default(false)->index();
            $table->timestamp('featured_until')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropIndex(['featured']);
            $table->dropIndex(['featured_until']);
            $table->dropColumn(['featured', 'featured_until']);
        });
    }
};
