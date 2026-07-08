<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('case_transfers', function (Blueprint $table) {
            $table->string('transfer_type')->default('staff')->after('case_id'); // staff | pathway
            $table->string('from_pathway')->nullable()->after('to_assignee');
            $table->string('to_pathway')->nullable()->after('from_pathway');
            $table->string('to_pathway_specific')->nullable()->after('to_pathway');
        });
    }

    public function down(): void
    {
        Schema::table('case_transfers', function (Blueprint $table) {
            $table->dropColumn(['transfer_type', 'from_pathway', 'to_pathway', 'to_pathway_specific']);
        });
    }
};
