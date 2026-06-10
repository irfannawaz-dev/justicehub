<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->string('adr_stage', 50)->default('ADR Intake')->after('litigation_stage_changed_at');
            $table->unsignedBigInteger('adr_stage_changed_by')->nullable()->after('adr_stage');
            $table->timestamp('adr_stage_changed_at')->nullable()->after('adr_stage_changed_by');
        });
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn(['adr_stage', 'adr_stage_changed_by', 'adr_stage_changed_at']);
        });
    }
};
