<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_uid')->unique();           // 'CL-02471'
            $table->string('case_ref')->unique();            // 'CA-02471'
            $table->string('encounter_id')->nullable();      // 'SE-09841'

            // Hub & assignment
            $table->string('hub_id');
            $table->string('assigned_to')->nullable();       // Staff name
            $table->foreignId('assigned_staff_id')->nullable()->constrained('staff')->nullOnDelete();

            // Client info
            $table->string('name');
            $table->string('father_husband_name')->nullable();
            $table->string('gender');
            $table->string('gender_other')->nullable();
            $table->integer('age');
            $table->string('cnic', 255)->nullable(); // stored encrypted, needs room
            $table->string('marital_status')->nullable();
            $table->string('religion')->nullable();
            $table->string('education_level')->nullable();
            $table->string('occupation')->nullable();
            $table->string('income_bracket')->nullable();
            $table->string('disability_status')->nullable();
            $table->string('primary_contact', 15)->nullable();
            $table->string('alternative_contact', 15)->nullable();
            $table->text('full_address')->nullable();
            $table->string('union_council')->nullable();
            $table->string('tehsil')->nullable();
            $table->string('district')->nullable();
            $table->string('language')->nullable();

            // Intake info
            $table->date('intake_date');
            $table->time('intake_time')->nullable();
            $table->string('mode')->nullable();              // Walk-in, Referral, etc.
            $table->string('source')->nullable();            // Self, NGO, Court, etc.
            $table->string('referral_source')->nullable();   // specific referral source
            $table->boolean('consent')->default(true);
            $table->text('no_consent_reason')->nullable();
            $table->boolean('returning_client')->default(false);
            $table->string('staff_receiving')->nullable();
            $table->string('staff_designation')->nullable();

            // Case classification
            $table->string('primary_issue');
            $table->string('secondary_issue')->nullable();
            $table->text('issue_description')->nullable();
            $table->string('urgency');                       // enum: Critical, High, Medium, Low
            $table->string('status')->default('Active');     // enum: Active, Pending Approval, etc.
            $table->string('disposition')->nullable();       // enum: adr, litigation, etc.
            $table->string('risk')->default('Low');          // enum: High, Medium, Low

            // SLA
            $table->boolean('sla_met')->default(true);

            // Vulnerability flags
            $table->boolean('is_gbv')->default(false);
            $table->boolean('is_child')->default(false);
            $table->boolean('is_minority')->default(false);
            $table->boolean('is_disability')->default(false);
            $table->boolean('is_underserved')->default(false);

            // Pathway assignment
            $table->string('assigned_pathway')->nullable();
            $table->string('pathway_specific')->nullable();
            $table->string('pathway_specific_other')->nullable();
            $table->string('pathway_govt_dept')->nullable();
            $table->string('pathway_ngo_name')->nullable();
            $table->text('pathway_other_details')->nullable();

            // Approval workflow
            $table->string('pathway_manager')->nullable();
            $table->string('approval_decision')->nullable(); // pending/approved/rejected
            $table->dateTime('requested_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('rejected_by')->nullable();
            $table->dateTime('rejected_at')->nullable();

            // Summary & tracking
            $table->text('summary')->nullable();
            $table->date('last_update')->nullable();

            // Flexibility
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys & indexes
            $table->foreign('hub_id')->references('id')->on('hubs');
            $table->index(['hub_id', 'status']);
            $table->index(['hub_id', 'disposition']);
            $table->index('primary_issue');
            $table->index('urgency');
            $table->index('intake_date');
            $table->index('assigned_staff_id');
        });

        // Pivot for multiple pathways per case
        Schema::create('case_pathway', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained()->cascadeOnDelete();
            $table->string('pathway_value');
            $table->timestamps();

            $table->index(['case_id', 'pathway_value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_pathway');
        Schema::dropIfExists('cases');
    }
};
