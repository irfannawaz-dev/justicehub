<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->unsignedBigInteger('external_case_id')->nullable()->after('id');
            $table->timestamp('external_synced_at')->nullable()->after('external_case_id');
            $table->index('external_case_id');
        });
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropIndex(['external_case_id']);
            $table->dropColumn(['external_case_id', 'external_synced_at']);
        });
    }
};
