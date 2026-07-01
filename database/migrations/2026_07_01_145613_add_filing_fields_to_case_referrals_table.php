<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('case_referrals', function (Blueprint $table) {
            $table->string('filing_status')->nullable()->after('referred_by'); // 'Filed' or 'Not Filed'
            $table->string('tracking_number')->nullable()->after('filing_status');
            $table->text('filing_justification')->nullable()->after('tracking_number');
        });
    }

    public function down(): void
    {
        Schema::table('case_referrals', function (Blueprint $table) {
            $table->dropColumn(['filing_status', 'tracking_number', 'filing_justification']);
        });
    }
};
