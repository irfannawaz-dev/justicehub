<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidence', function (Blueprint $table) {
            $table->id();
            $table->string('evidence_uid')->unique();        // 'EV-001'
            $table->string('type');                           // recognition, integration, etc.
            $table->string('title');
            $table->text('summary')->nullable();
            $table->date('date');
            $table->boolean('verified')->default(false);
            $table->string('verified_by')->nullable();
            $table->date('verified_date')->nullable();
            $table->string('issuer')->nullable();
            $table->string('hub_id')->nullable();             // null = all hubs
            $table->string('document_ref')->nullable();
            $table->json('tags')->nullable();
            $table->string('linked_indicator')->nullable();   // G1, O1.1, etc.
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('hub_id')->references('id')->on('hubs')->nullOnDelete();
            $table->index('type');
            $table->index('linked_indicator');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence');
    }
};
