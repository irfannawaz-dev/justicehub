<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Load hub district map
        $hubs = DB::table('hubs')->pluck('district', 'id');

        // Get all cases grouped by hub, ordered by id (creation order)
        $cases = DB::table('cases')
            ->orderBy('hub_id')
            ->orderBy('id')
            ->get(['id', 'hub_id']);

        $counters = [];

        foreach ($cases as $case) {
            $hubId    = $case->hub_id ?? 'unknown';
            $district = isset($hubs[$hubId])
                ? str_replace(' ', '-', $hubs[$hubId])
                : $hubId;

            $counters[$hubId] = ($counters[$hubId] ?? 0) + 1;
            $seq = $counters[$hubId];

            DB::table('cases')->where('id', $case->id)->update([
                'case_uid' => 'LAS-' . $district . '-' . $seq,
                'case_ref' => 'LAS-' . $district . '-REF-' . $seq,
            ]);
        }
    }

    public function down(): void
    {
        // Restore sequential CL- format
        $cases   = DB::table('cases')->orderBy('id')->get(['id']);
        $counter = 10001;
        foreach ($cases as $case) {
            DB::table('cases')->where('id', $case->id)->update([
                'case_uid' => 'CL-' . str_pad($counter, 5, '0', STR_PAD_LEFT),
                'case_ref' => 'CA-' . str_pad($counter, 5, '0', STR_PAD_LEFT),
            ]);
            $counter++;
        }
    }
};
