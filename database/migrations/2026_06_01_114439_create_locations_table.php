<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('province', 60);
            $table->string('district', 100)->index();
            $table->string('taluka', 100)->nullable();
            $table->string('union_council', 200)->nullable();
            $table->string('hub_id', 20)->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
