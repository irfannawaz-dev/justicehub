<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->string('cnic_hash', 64)->nullable()->after('cnic')->index();
        });

        // Backfill existing records
        $cases = \App\Models\CaseRecord::whereNotNull('cnic')->get();
        foreach ($cases as $case) {
            if ($case->cnic) {
                $case->cnic_hash = hash('sha256', $case->cnic);
                $case->saveQuietly();
            }
        }
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn('cnic_hash');
        });
    }
};
