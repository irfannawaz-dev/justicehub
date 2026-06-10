<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('emp_id')->nullable()->unique()->after('id');
            $table->string('contact_number', 20)->nullable()->after('email');
            $table->string('designation')->nullable()->after('role');
            $table->string('department')->nullable()->after('designation');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['emp_id', 'contact_number', 'designation', 'department']);
        });
    }
};
