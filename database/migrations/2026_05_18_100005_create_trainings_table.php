<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();              // 'SOP-CORE'
            $table->string('name');
            $table->string('category')->nullable();         // sops, safeguarding, etc.
            $table->boolean('mandatory')->default(false);
            $table->string('refresh')->nullable();          // annual, biennial, one-off
            $table->json('audience')->nullable();            // ['Lawyer', 'Paralegal', ...]
            $table->text('description')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
