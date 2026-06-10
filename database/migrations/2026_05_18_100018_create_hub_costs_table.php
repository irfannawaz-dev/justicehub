<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hub_costs', function (Blueprint $table) {
            $table->id();
            $table->string('hub_id');
            $table->string('quarter');                        // 'Q1 2026'
            $table->decimal('cost_per_case', 12, 2)->nullable();
            $table->decimal('total_operational_cost', 14, 2)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('hub_id')->references('id')->on('hubs')->cascadeOnDelete();
            $table->index(['hub_id', 'quarter']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hub_costs');
    }
};
