<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Set parent_value on existing complaint department options (Provincial Ombudsman),
     * duplicate them for Federal Ombudsman, and add BISP for both.
     * Safe to run on fresh or existing databases — skips duplicates.
     */
    public function up(): void
    {
        $group = 'intake.complaint_department';

        // 1. Set parent on existing options that have no parent yet
        DB::table('lookups')
            ->where('group_key', $group)
            ->whereNull('parent_value')
            ->update(['parent_value' => 'Provincial Ombudsman / Mohtasib']);

        // 2. Duplicate all Provincial options for Federal Ombudsman (skip if already exists)
        $provincialOpts = DB::table('lookups')
            ->where('group_key', $group)
            ->where('parent_value', 'Provincial Ombudsman / Mohtasib')
            ->orderBy('sort_order')
            ->get();

        $maxOrder = DB::table('lookups')
            ->where('group_key', $group)
            ->max('sort_order') ?? 0;

        $i = 1;
        foreach ($provincialOpts as $opt) {
            $exists = DB::table('lookups')
                ->where('group_key', $group)
                ->where('value', $opt->value)
                ->where('parent_value', 'Federal Ombudsman')
                ->exists();

            if (! $exists) {
                DB::table('lookups')->insert([
                    'group_key'    => $group,
                    'value'        => $opt->value,
                    'label'        => $opt->label,
                    'sort_order'   => $maxOrder + $i,
                    'is_active'    => $opt->is_active,
                    'parent_value' => 'Federal Ombudsman',
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
                $i++;
            }
        }

        // 3. Add BISP for both parents (skip if already exists)
        foreach (['Provincial Ombudsman / Mohtasib', 'Federal Ombudsman'] as $parent) {
            $exists = DB::table('lookups')
                ->where('group_key', $group)
                ->where('value', 'BISP')
                ->where('parent_value', $parent)
                ->exists();

            if (! $exists) {
                $max = DB::table('lookups')
                    ->where('group_key', $group)
                    ->where('parent_value', $parent)
                    ->max('sort_order') ?? 0;

                DB::table('lookups')->insert([
                    'group_key'    => $group,
                    'value'        => 'BISP',
                    'label'        => 'BISP',
                    'sort_order'   => $max + 1,
                    'is_active'    => true,
                    'parent_value' => $parent,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }
    }

    /**
     * Reverse: remove Federal Ombudsman duplicates and BISP, clear parent_value.
     */
    public function down(): void
    {
        $group = 'intake.complaint_department';

        DB::table('lookups')
            ->where('group_key', $group)
            ->where('parent_value', 'Federal Ombudsman')
            ->delete();

        DB::table('lookups')
            ->where('group_key', $group)
            ->where('value', 'BISP')
            ->delete();

        DB::table('lookups')
            ->where('group_key', $group)
            ->update(['parent_value' => null]);
    }
};
