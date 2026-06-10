<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->string('feedback_uid')->unique();        // 'FB-016'
            $table->foreignId('case_id')->nullable()->constrained()->nullOnDelete();
            $table->string('client_name');
            $table->boolean('is_anonymous')->default(false);
            $table->string('hub_id');
            $table->string('service');                        // from lookups
            $table->string('lawyer')->nullable();
            $table->date('date');
            $table->string('channel');                        // in-person, sms, phone
            $table->tinyInteger('score_overall')->unsigned();  // 1-5
            $table->tinyInteger('score_helpfulness')->unsigned();
            $table->tinyInteger('score_respect')->unsigned();
            $table->string('understood_rights')->nullable();   // yes, partial, no
            $table->string('would_recommend')->nullable();     // yes, maybe, no
            $table->text('comment')->nullable();
            $table->boolean('consent_to_share')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('hub_id')->references('id')->on('hubs');
            $table->index(['hub_id', 'date']);
            $table->index('case_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
