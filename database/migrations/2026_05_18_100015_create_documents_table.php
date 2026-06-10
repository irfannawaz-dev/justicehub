<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_uid')->unique();        // 'DOC-0241'
            $table->foreignId('case_id')->constrained()->cascadeOnDelete();
            $table->string('type');                           // id, consent, filing, evidence, etc.
            $table->string('name');
            $table->date('added_date');
            $table->string('added_by');
            $table->string('source');                         // uploaded, received, generated
            $table->string('status')->default('draft');       // draft, signed, submitted, etc.
            $table->string('confidentiality')->default('restricted');
            $table->string('document_ref')->nullable();
            $table->integer('pages')->nullable();
            $table->string('file_path')->nullable();          // actual file storage path
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['case_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
