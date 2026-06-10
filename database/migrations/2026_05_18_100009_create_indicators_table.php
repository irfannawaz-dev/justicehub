<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indicators', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();               // 'G1', 'O1.1', 'OP1.5'
            $table->string('level');                          // Goal, Outcome 1, Output 1, etc.
            $table->text('name');
            $table->string('priority');                       // P0, P1
            $table->string('cadence');                        // Monthly, Quarterly, Annual
            $table->decimal('target', 12, 2)->default(0);
            $table->decimal('actual', 12, 2)->default(0);
            $table->string('unit');                           // %, people, cases, days, PKR
            $table->string('type')->default('count');         // count, pct
            $table->boolean('is_inverse')->default(false);    // lower is better (e.g. turnaround days)
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('indicator_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('indicator_code');
            $table->string('month_label');                    // 'Nov', 'Dec'
            $table->string('month_iso');                      // '2025-11'
            $table->decimal('value', 12, 2);
            $table->timestamps();

            $table->foreign('indicator_code')->references('code')->on('indicators')->cascadeOnDelete();
            $table->index(['indicator_code', 'month_iso']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicator_snapshots');
        Schema::dropIfExists('indicators');
    }
};
