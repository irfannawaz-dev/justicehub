<?php

namespace Database\Seeders;

use App\Models\Lookup;
use Illuminate\Database\Seeder;

class LookupSeeder extends Seeder
{
    public function run(): void
    {
        $allGroups = config('lookups');

        if (!$allGroups) {
            $this->command->error('config/lookups.php not found or empty.');
            return;
        }

        $inserted = 0;

        foreach ($allGroups as $groupKey => $options) {
            foreach ($options as $sortOrder => $option) {

                // Options can be:
                // 1. Simple string:  'Walk-in'
                // 2. Associative array: ['value' => 'adr', 'label' => 'ADR', 'parent' => '...', 'meta' => [...]]

                if (is_string($option)) {
                    $value = $option;
                    $label = null;
                    $parent = null;
                    $meta = null;
                } elseif (is_array($option)) {
                    $value = $option['value'] ?? $option[0] ?? '';
                    $label = $option['label'] ?? null;
                    $parent = $option['parent'] ?? null;
                    $meta = $option['meta'] ?? null;
                } else {
                    continue;
                }

                if (empty($value)) continue;

                Lookup::firstOrCreate(
                    [
                        'group_key' => $groupKey,
                        'value' => $value,
                        'parent_value' => $parent,
                    ],
                    [
                        'label' => $label,
                        'sort_order' => $sortOrder,
                        'is_active' => true,
                        'meta' => $meta,
                    ]
                );

                $inserted++;
            }
        }

        $this->command->info("LookupSeeder: {$inserted} options processed across " . count($allGroups) . " groups.");
    }
}
