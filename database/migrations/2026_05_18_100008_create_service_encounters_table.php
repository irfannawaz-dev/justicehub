<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_encounters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('type');                          // from lookups: Intake, Assessment, etc.
            $table->string('performed_by');
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->text('note')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['case_id', 'date']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_encounters');
    }
};
