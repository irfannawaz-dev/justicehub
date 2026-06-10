<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->string('litigation_stage', 50)->default('Filed')->after('status');
            $table->unsignedBigInteger('litigation_stage_changed_by')->nullable()->after('litigation_stage');
            $table->timestamp('litigation_stage_changed_at')->nullable()->after('litigation_stage_changed_by');
        });
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn(['litigation_stage', 'litigation_stage_changed_by', 'litigation_stage_changed_at']);
        });
    }
};
