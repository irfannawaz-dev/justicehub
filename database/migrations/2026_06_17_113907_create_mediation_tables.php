<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mediation_parties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('case_id');
            $table->foreign('case_id')->references('id')->on('cases')->cascadeOnDelete();
            $table->string('name');
            $table->string('role')->default('Respondent');
            $table->string('phone')->nullable();
            $table->text('note')->nullable();
            $table->enum('consent_status', ['awaiting', 'agreed', 'declined'])->default('awaiting');
            $table->timestamps();
        });

        Schema::create('mediation_diary', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('case_id');
            $table->foreign('case_id')->references('id')->on('cases')->cascadeOnDelete();
            $table->date('session_date');
            $table->date('next_session_date')->nullable();
            $table->text('what_happened');
            $table->text('note_for_next_session')->nullable();
            $table->string('logged_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mediation_diary');
        Schema::dropIfExists('mediation_parties');
    }
};
