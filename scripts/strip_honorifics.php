<?php
// Step 1: Apply assigned_to from map
$map = json_decode(file_get_contents(storage_path('app/assigned_to_map.json')), true);
$count = 0;
foreach ($map as $uid => $name) {
    $updated = DB::table('cases')->where('case_uid', $uid)->update(['assigned_to' => $name]);
    if ($updated) $count++;
}
echo "Assigned: {$count} cases\n";

// Step 2: Strip honorifics in PHP (MySQL 5.7 has no REGEXP_REPLACE)
$prefixes = ['Mr. ', 'Ms. ', 'Mrs. ', 'Dr. ', 'Mst. ', 'Mr ', 'Ms ', 'Mrs ', 'Dr '];
$rows = DB::table('cases')->whereNotNull('assigned_to')->select('id', 'assigned_to')->get();
$stripped = 0;
foreach ($rows as $row) {
    $name = $row->assigned_to;
    foreach ($prefixes as $p) {
        if (str_starts_with($name, $p)) {
            $name = trim(substr($name, strlen($p)));
            break;
        }
    }
    if ($name !== $row->assigned_to) {
        DB::table('cases')->where('id', $row->id)->update(['assigned_to' => $name]);
        $stripped++;
    }
}
echo "Honorifics stripped from {$stripped} cases.\n";

// Step 3: Show samples
$samples = DB::table('cases')->whereNotNull('assigned_to')->distinct()->limit(10)->pluck('assigned_to');
foreach ($samples as $name) echo "  - {$name}\n";
