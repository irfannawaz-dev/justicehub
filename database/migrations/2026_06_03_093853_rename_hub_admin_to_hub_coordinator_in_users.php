<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('role', 'hub-admin')->update(['role' => 'hub-coordinator']);
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'hub-coordinator')->update(['role' => 'hub-admin']);
    }
};
