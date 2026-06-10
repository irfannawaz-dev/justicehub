<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pulse_surveys', function (Blueprint $table) {
            $table->id();
            $table->string('pulse_uid')->unique();           // 'PS-001'
            $table->foreignId('outreach_id')->nullable()->constrained('outreach_activities')->nullOnDelete();
            $table->string('session')->nullable();
            $table->date('date');
            $table->integer('respondent_count')->default(0);
            $table->decimal('pre_score', 5, 2)->nullable();
            $table->decimal('post_score', 5, 2)->nullable();
            $table->string('will_apply')->nullable();         // yes, no, maybe
            $table->decimal('would_recommend_pct', 5, 2)->nullable();
            $table->json('demographics')->nullable();         // gender/age breakdown
            $table->text('comment')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pulse_surveys');
    }
};
