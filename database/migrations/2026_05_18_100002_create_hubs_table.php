<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hubs', function (Blueprint $table) {
            $table->string('id')->primary();          // 'JH-HYD-01'
            $table->string('name');
            $table->string('district');
            $table->string('province')->default('Sindh');
            $table->tinyInteger('tier')->default(1);
            $table->integer('staff_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        // Add FK on users table now that hubs exists
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('hub_id')->references('id')->on('hubs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['hub_id']);
        });
        Schema::dropIfExists('hubs');
    }
};
