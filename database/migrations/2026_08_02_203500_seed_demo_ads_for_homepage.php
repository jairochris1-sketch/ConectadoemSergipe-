<?php

use App\Services\DemoAdSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        try {
            DemoAdSeeder::seedIfNeeded();
        } catch (\Throwable $e) {
            // Silence any seeding exception during migration
        }
    }

    public function down(): void
    {
        // No rollback needed
    }
};
