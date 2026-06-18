<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_referrals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->string('referred_to');
            $table->string('contact_person')->nullable();
            $table->string('contact_phone')->nullable();
            $table->date('referral_date');
            $table->text('reason')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->string('status')->default('Pending');
            $table->text('outcome')->nullable();
            $table->string('referred_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_referrals');
    }
};
