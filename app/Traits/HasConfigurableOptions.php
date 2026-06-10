<?php

namespace App\Traits;

use App\Models\Lookup;

/**
 * Provides config-driven dropdown options from the lookups table.
 * Use in models or controllers to get dropdown values.
 */
trait HasConfigurableOptions
{
    /**
     * Get options for a lookup group as [['value' => ..., 'label' => ..., 'meta' => ...], ...]
     */
    public static function getOptions(string $groupKey, ?string $parentValue = null): array
    {
        return Lookup::getOptions($groupKey, $parentValue);
    }

    /**
     * Get a simple value => label map for Blade select dropdowns.
     */
    public static function selectOptions(string $groupKey, ?string $parentValue = null): array
    {
        return Lookup::selectOptions($groupKey, $parentValue);
    }
}
