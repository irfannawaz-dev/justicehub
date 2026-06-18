<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('case_referrals', function (Blueprint $table) {
            $table->string('focal_person_name')->nullable()->after('contact_phone');
            $table->string('focal_person_designation')->nullable()->after('focal_person_name');
            $table->string('focal_person_phone')->nullable()->after('focal_person_designation');
            $table->string('focal_person_email')->nullable()->after('focal_person_phone');
            $table->string('partner_tracking_ref')->nullable()->after('focal_person_email');
            $table->timestamp('closed_at')->nullable()->after('outcome');
            $table->string('closed_outcome')->nullable()->after('closed_at');
            $table->text('closed_note')->nullable()->after('closed_outcome');
        });
    }

    public function down(): void
    {
        Schema::table('case_referrals', function (Blueprint $table) {
            $table->dropColumn([
                'focal_person_name', 'focal_person_designation',
                'focal_person_phone', 'focal_person_email',
                'partner_tracking_ref',
                'closed_at', 'closed_outcome', 'closed_note',
            ]);
        });
    }
};
