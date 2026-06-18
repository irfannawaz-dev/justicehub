<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_referral_threads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('case_referral_id')->constrained('case_referrals')->cascadeOnDelete();
            $table->string('direction');   // from_partner | from_us
            $table->string('type');        // Email | Phone | Meeting | Letter
            $table->date('thread_date');
            $table->text('note')->nullable();
            $table->string('logged_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_referral_threads');
    }
};
