<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_studies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('narrative');
            $table->text('impact_statement')->nullable();
            $table->text('lessons_learned')->nullable();
            $table->string('replication_potential')->nullable();  // High, Medium, Low
            $table->text('supporting_evidence')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_studies');
    }
};
