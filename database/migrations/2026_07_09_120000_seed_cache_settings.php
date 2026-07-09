<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->insertOrIgnore([
            ['key' => 'cache_enabled', 'value' => 'on',  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'cache_ttl',     'value' => '300', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', ['cache_enabled', 'cache_ttl'])->delete();
    }
};
