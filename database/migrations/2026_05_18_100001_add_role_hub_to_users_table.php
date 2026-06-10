<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('data-entry')->after('name');
            $table->string('hub_id')->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('hub_id');
            $table->json('meta')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'hub_id', 'is_active', 'meta']);
        });
    }
};
