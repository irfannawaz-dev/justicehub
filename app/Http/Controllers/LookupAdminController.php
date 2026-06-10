<?php

namespace App\Http\Controllers;

use App\Models\Lookup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LookupAdminController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // Authorization guard — all methods require lookups.manage
    // ─────────────────────────────────────────────────────────────

    private function guard(Request $request): void
    {
        abort_unless($request->user()->can('lookups.manage'), 403, 'Only the Head (Super Admin) can manage lookup options.');
    }

    // ─────────────────────────────────────────────────────────────
    // Add a new option to an existing group
    // ─────────────────────────────────────────────────────────────

    public function storeOption(Request $request)
    {
        $this->guard($request);

        $request->validate([
            'group_key'    => 'required|string|max:80',
            'value'        => 'required|string|max:100',
            'label'        => 'required|string|max:150',
            'parent_value' => 'nullable|string|max:100',
        ]);

        // Prevent duplicate value within same group
        $exists = Lookup::where('group_key', $request->group_key)
            ->where('value', $request->value)
            ->exists();

        if ($exists) {
            return back()->withErrors(['value' => 'A "' . $request->value . '" option already exists in this group.'])->withInput();
        }

        // Place at end of current group
        $maxOrder = Lookup::where('group_key', $request->group_key)->max('sort_order') ?? 0;

        Lookup::create([
            'group_key'    => $request->group_key,
            'value'        => $request->value,
            'label'        => $request->label,
            'sort_order'   => $maxOrder + 1,
            'is_active'    => true,
            'parent_value' => $request->parent_value ?: null,
            'meta'         => null,
        ]);

        Lookup::clearCache($request->group_key);

        return back()->with('success', "Option \"{$request->label}\" added to {$request->group_key}.");
    }

    // ─────────────────────────────────────────────────────────────
    // Update label and/or sort_order for an existing option
    // ─────────────────────────────────────────────────────────────

    public function updateOption(Request $request, Lookup $lookup)
    {
        $this->guard($request);

        $request->validate([
            'label'      => 'required|string|max:150',
            'sort_order' => 'required|integer|min:0|max:9999',
        ]);

        $lookup->update([
            'label'      => $request->label,
            'sort_order' => $request->sort_order,
        ]);

        Lookup::clearCache($lookup->group_key);

        return back()->with('success', "Option \"{$lookup->label}\" updated.");
    }

    // ─────────────────────────────────────────────────────────────
    // Toggle is_active (soft-disable, not delete)
    // ─────────────────────────────────────────────────────────────

    public function toggleOption(Request $request, Lookup $lookup)
    {
        $this->guard($request);

        $lookup->update(['is_active' => !$lookup->is_active]);

        Lookup::clearCache($lookup->group_key);

        $state = $lookup->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Option \"{$lookup->label}\" {$state}.");
    }

    // ─────────────────────────────────────────────────────────────
    // Create a brand-new group_key with its first option
    // ─────────────────────────────────────────────────────────────

    public function storeGroup(Request $request)
    {
        $this->guard($request);

        $request->validate([
            'group_key'   => [
                'required', 'string', 'max:80',
                'regex:/^[a-z0-9_\-.]+$/',
                // group_key must not already exist
                Rule::unique('lookups', 'group_key'),
            ],
            'first_value' => 'required|string|max:100',
            'first_label' => 'required|string|max:150',
        ]);

        Lookup::create([
            'group_key'  => $request->group_key,
            'value'      => $request->first_value,
            'label'      => $request->first_label,
            'sort_order' => 1,
            'is_active'  => true,
        ]);

        Lookup::clearCache($request->group_key);

        return back()->with('success', "Group \"{$request->group_key}\" created with first option.");
    }

    // ─────────────────────────────────────────────────────────────
    // Bulk reorder: accepts JSON array of [{id, sort_order}]
    // Used by the drag-and-drop up/down buttons
    // ─────────────────────────────────────────────────────────────

    public function reorderGroup(Request $request)
    {
        $this->guard($request);

        $request->validate([
            'group_key' => 'required|string|max:80',
            'order'     => 'required|array|min:1',
            'order.*.id'         => 'required|integer|exists:lookups,id',
            'order.*.sort_order' => 'required|integer|min:0|max:9999',
        ]);

        foreach ($request->order as $item) {
            Lookup::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        Lookup::clearCache($request->group_key);

        return response()->json(['ok' => true]);
    }
}
