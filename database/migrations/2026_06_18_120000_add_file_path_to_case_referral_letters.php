<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('case_referral_letters', function (Blueprint $table) {
            $table->string('file_path')->nullable()->after('note');
            $table->string('file_name')->nullable()->after('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('case_referral_letters', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'file_name']);
        });
    }
};
