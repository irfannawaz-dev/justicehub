<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lookups', function (Blueprint $table) {
            $table->id();
            $table->string('group_key', 100);
            $table->string('value', 255);
            $table->string('label', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('parent_value', 255)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['group_key', 'is_active', 'sort_order'], 'idx_group_active_sort');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lookups');
    }
};
