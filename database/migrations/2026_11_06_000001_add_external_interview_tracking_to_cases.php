<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->unsignedBigInteger('jh_cms_interview_id')->nullable()->index()->after('external_case_id');
            $table->string('jh_cms_interview_status', 32)->nullable()->after('jh_cms_interview_id');
        });
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropIndex(['jh_cms_interview_id']);
            $table->dropColumn(['jh_cms_interview_id', 'jh_cms_interview_status']);
        });
    }
};
