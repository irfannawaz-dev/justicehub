<?php
// Step 1: Apply assigned_to from map
$map = json_decode(file_get_contents(storage_path('app/assigned_to_map.json')), true);
$count = 0;
foreach ($map as $uid => $name) {
    $updated = DB::table('cases')->where('case_uid', $uid)->update(['assigned_to' => $name]);
    if ($updated) $count++;
}
echo "Assigned: {$count} cases\n";

// Step 2: Strip honorifics
DB::statement("UPDATE cases SET assigned_to = TRIM(REGEXP_REPLACE(assigned_to, '^(Mr\\.|Ms\\.|Mrs\\.|Dr\\.|Mst\\.|Mr |Ms |Mrs |Dr ) *', ''))");
echo "Honorifics stripped.\n";

// Step 3: Show samples
$samples = DB::table('cases')->whereNotNull('assigned_to')->distinct()->limit(10)->pluck('assigned_to');
foreach ($samples as $name) echo "  - {$name}\n";
