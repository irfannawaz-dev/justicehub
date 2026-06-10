<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->string('id')->primary();              // 'P-001'
            $table->string('name');
            $table->string('category');                    // from lookups
            $table->string('type')->nullable();
            $table->string('focal_person')->nullable();
            $table->integer('active_referrals')->default(0);
            $table->integer('completed_referrals')->default(0);
            $table->integer('failed_referrals')->default(0);
            $table->integer('avg_response_hours')->nullable();
            $table->date('last_referral_date')->nullable();
            $table->date('mou_expires')->nullable();
            $table->string('mou_status')->nullable();      // active/expiring/expired
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('hub_partner', function (Blueprint $table) {
            $table->string('hub_id');
            $table->string('partner_id');
            $table->primary(['hub_id', 'partner_id']);
            $table->foreign('hub_id')->references('id')->on('hubs')->cascadeOnDelete();
            $table->foreign('partner_id')->references('id')->on('partners')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hub_partner');
        Schema::dropIfExists('partners');
    }
};
