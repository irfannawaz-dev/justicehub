<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reflections', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('staff');
            $table->string('hub_id')->nullable();
            $table->string('title');
            $table->text('description');
            $table->text('key_learning')->nullable();
            $table->text('implementation_notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('hub_id')->references('id')->on('hubs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reflections');
    }
};
