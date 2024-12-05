<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->json('allowed_countries')->nullable()->after('is_active');
            $table->integer('hourly_limit')->default(0)->after('allowed_countries');
            $table->integer('daily_limit')->default(0)->after('hourly_limit');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['allowed_countries', 'hourly_limit', 'daily_limit']);
        });
    }
};