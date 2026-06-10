<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('complaint_uid')->unique();       // 'CMP-021'
            $table->foreignId('case_id')->nullable()->constrained()->nullOnDelete();
            $table->date('submitted_date');
            $table->string('submitted_by');
            $table->boolean('is_anonymous')->default(false);
            $table->string('channel');                        // in-person, phone, written, paralegal
            $table->string('category');                       // from lookups
            $table->string('severity');                       // critical, high, medium, low
            $table->integer('sla_days');
            $table->text('description');
            $table->string('hub_id');
            $table->string('assigned_to')->nullable();
            $table->string('status')->default('open');        // open, in-progress, resolved, escalated
            $table->date('resolved_date')->nullable();
            $table->text('resolution')->nullable();
            $table->string('client_satisfied')->nullable();   // yes, no, n.a.
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('hub_id')->references('id')->on('hubs');
            $table->index(['hub_id', 'severity']);
            $table->index(['hub_id', 'status']);
            $table->index('submitted_date');
        });

        Schema::create('complaint_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('performed_by');
            $table->text('note');
            $table->timestamps();

            $table->index('complaint_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint_actions');
        Schema::dropIfExists('complaints');
    }
};
