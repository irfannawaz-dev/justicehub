<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outreach_activities', function (Blueprint $table) {
            $table->id();
            $table->string('outreach_uid')->unique();        // 'OR-0701'
            $table->date('date');
            $table->string('hub_id');
            $table->string('type');                           // Legal Literacy, Paralegal Outreach, Awareness
            $table->string('location');
            $table->string('facilitator');
            $table->integer('total_participants')->default(0);
            $table->integer('female_participants')->default(0);
            $table->integer('minority_participants')->default(0);
            $table->integer('disability_participants')->default(0);
            $table->string('topic')->nullable();
            $table->boolean('naz_promoted')->default(false);
            $table->boolean('slacc')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('hub_id')->references('id')->on('hubs');
            $table->index(['hub_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outreach_activities');
    }
};
