<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_surveys', function (Blueprint $t) {
            $t->id();
            $t->string('survey_uid', 20)->unique();
            $t->foreignId('case_id')->nullable()->constrained('cases')->nullOnDelete();
            $t->string('hub_id', 20);
            $t->string('enumerator_name')->nullable();
            $t->boolean('consent')->default(true);

            // Section A
            $t->date('visit_date')->nullable();
            $t->date('service_date')->nullable();
            $t->string('service_type', 60)->nullable();
            $t->boolean('first_visit')->nullable();

            // Section B — Access & Welcome (1-5 scale)
            $t->tinyInteger('q11_access')->nullable();
            $t->tinyInteger('q12_reception')->nullable();
            $t->tinyInteger('q13_explanation')->nullable();
            $t->tinyInteger('q14_waiting')->nullable();
            $t->string('q15_difficulty', 120)->nullable();

            // Section C — Listening & Dignity
            $t->string('q16_listened', 30)->nullable();
            $t->string('q17_comfortable', 30)->nullable();
            $t->tinyInteger('q18_understood')->nullable();
            $t->string('q19_fair_treatment', 30)->nullable();

            // Section D — Confidentiality & Consent
            $t->string('q20_info_safety', 30)->nullable();
            $t->string('q21_data_explained', 30)->nullable();
            $t->tinyInteger('q22_confidence')->nullable();
            $t->string('q23_complaint_info', 30)->nullable();

            // Section E — Service Quality & Referrals
            $t->string('q24_advice_useful', 30)->nullable();
            $t->string('q25_referral_clarity', 30)->nullable();
            $t->string('q26_next_steps', 30)->nullable();
            $t->string('q27_clarity', 30)->nullable();

            // Section F — Overall Satisfaction & Trust
            $t->tinyInteger('q28_satisfaction')->nullable();
            $t->string('q29_resolution_help', 30)->nullable();
            $t->string('q30_recommend', 30)->nullable();
            $t->tinyInteger('q31_trust')->nullable();

            // Section G — Open Feedback
            $t->text('q32_helpful_part')->nullable();
            $t->text('q33_improvement')->nullable();
            $t->text('q34_additional')->nullable();

            $t->json('meta')->nullable();
            $t->timestamps();

            $t->index(['hub_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_surveys');
    }
};
