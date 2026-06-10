<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('staff_uid')->unique();          // 'STF-001'
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('initials', 5);
            $table->string('role');                          // Lawyer, Paralegal, etc.
            $table->string('hub_id');
            $table->string('status')->default('active');
            $table->date('joined_date');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('hub_id')->references('id')->on('hubs')->cascadeOnDelete();
        });

        Schema::create('staff_trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('training_id')->constrained()->cascadeOnDelete();
            $table->date('completed_on');
            $table->date('expires')->nullable();
            $table->string('delivered_by')->nullable();
            $table->string('certificate_ref')->nullable();
            $table->timestamps();

            $table->index(['staff_id', 'training_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_trainings');
        Schema::dropIfExists('staff');
    }
};
