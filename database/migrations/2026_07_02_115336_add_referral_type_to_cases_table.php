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
        Schema::table('cases', function (Blueprint $table) {
            $table->string('referral_type')->nullable()->after('referral_source');             // Incoming | Outgoing
            $table->string('referral_contact_person')->nullable()->after('referral_type');     // Name of person/office who referred them in
        });
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn(['referral_type', 'referral_contact_person']);
        });
    }
};
