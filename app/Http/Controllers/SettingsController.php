<?php

namespace App\Http\Controllers;

use App\Models\FinanceConfig;
use App\Models\Hub;
use App\Models\Lookup;
use App\Services\DashboardMetricsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $lookupData       = null;
        $lookupGroupKeys  = collect();
        $activeLookupGroup = null;
        $activeLookupOptions = collect();
        $lookupTotalGroups = 0;
        $lookupTotalOptions = 0;

        if ($request->user()->can('lookups.manage')) {
            // Only fetch group keys (lightweight — one column, no options)
            $lookupGroupKeys = Lookup::select('group_key')
                ->distinct()
                ->orderBy('group_key')
                ->pluck('group_key');

            $lookupTotalGroups  = $lookupGroupKeys->count();
            $lookupTotalOptions = Lookup::count();

            // Only load options for the selected group
            $activeLookupGroup = $request->input('lookup_group');
            if ($activeLookupGroup && $lookupGroupKeys->contains($activeLookupGroup)) {
                $activeLookupOptions = Lookup::where('group_key', $activeLookupGroup)
                    ->orderBy('sort_order')
                    ->get();
            } else {
                $activeLookupGroup = null;
            }

            // Keep $lookupData for backward-compat with view count expressions
            $lookupData = $activeLookupGroup
                ? collect([$activeLookupGroup => $activeLookupOptions])
                : collect();
        }

        // Location data for admin
        $locationData = null;
        if ($request->user()->can('lookups.manage')) {
            $locationData = DB::table('locations')
                ->select('district', DB::raw('COUNT(DISTINCT taluka) as taluka_count'), DB::raw('COUNT(*) as uc_count'))
                ->groupBy('district')
                ->orderBy('district')
                ->get();
        }

        $partners = \App\Models\Partner::orderBy('category')->orderBy('name')->get();

        // Merge lookup-defined categories with any already used in the partners table
        // so existing partner categories always appear even if lookups are empty
        $partnerCategories = DB::table('lookups')
            ->where('group_key', 'partner_category')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->pluck('label')
            ->merge(
                \App\Models\Partner::distinct()->orderBy('category')->pluck('category')->filter()
            )
            ->unique()
            ->sort()
            ->values();

        $moduleSettings = DB::table('settings')
            ->where('key', 'like', 'module_%')
            ->pluck('value', 'key');

        // Cache settings
        $cacheSettings = [
            'enabled' => DB::table('settings')->where('key', 'cache_enabled')->value('value') ?? 'on',
            'ttl'     => DB::table('settings')->where('key', 'cache_ttl')->value('value') ?? '300',
        ];

        return view('settings.index', compact(
            'lookupData', 'lookupGroupKeys', 'activeLookupGroup',
            'activeLookupOptions', 'lookupTotalGroups', 'lookupTotalOptions',
            'locationData', 'partners', 'partnerCategories', 'moduleSettings',
            'cacheSettings'
        ));
    }

    public function setHub(Request $request)
    {
        // Only global-scope roles can switch hubs
        abort_unless($request->user()->canSeeAllHubs(), 403);

        $validIds = Hub::pluck('id')->push('all')->toArray();
        $validated = $request->validate([
            'hub_id' => ['required', 'string', Rule::in($validIds)],
        ]);

        session(['active_hub' => $validated['hub_id']]);

        return back();
    }

    public function setTheme(Request $request)
    {
        $validated = $request->validate([
            'theme' => ['required', Rule::in(['light', 'dark'])],
        ]);

        session(['theme' => $validated['theme']]);

        if ($request->expectsJson()) {
            return response()->json(['theme' => $validated['theme']]);
        }

        return back();
    }

    public function setLocale(Request $request)
    {
        $validated = $request->validate([
            'locale' => ['required', Rule::in(['en', 'sd', 'ur'])],
        ]);

        $user = $request->user();
        $meta = $user->meta ?? [];
        $meta['locale'] = $validated['locale'];
        $user->update(['meta' => $meta]);

        session(['locale' => $validated['locale']]);
        app()->setLocale($validated['locale']);

        return back();
    }

    public function updateFinance(Request $request)
    {
        $request->validate([
            'cost_per_case' => 'required|numeric|min:0',
            'overhead_pct'  => 'required|numeric|min:0|max:100',
        ]);

        $config = FinanceConfig::current() ?? new FinanceConfig();
        $existing = $config->config ?? [];

        $existing['targets']['costPerCase'] = (float) $request->cost_per_case;
        $existing['overheadPct']            = (float) $request->overhead_pct;
        $existing['asOf']                   = now()->toDateString();
        $existing['submittedBy']            = auth()->user()->name;

        $config->config     = $existing;
        $config->updated_by = auth()->user()->name;
        $config->notes      = $request->notes ?? 'Updated via UI';
        $config->save();

        return back()->with('success', 'Finance configuration updated.');
    }

    // ── Training Course Management ──────────────────────────────

    public function storeTraining(Request $request)
    {
        $request->validate([
            'code'          => 'required|string|max:30|unique:trainings,code',
            'name'          => 'required|string|max:200',
            'refresh_value' => 'required|integer|min:1|max:365',
            'refresh_unit'  => 'required|in:days,months,one-off',
            'mandatory'     => 'nullable',
        ]);

        $refresh = $request->refresh_unit === 'one-off'
            ? 'one-off'
            : $request->refresh_value . ($request->refresh_unit === 'days' ? 'd' : 'mo');

        \App\Models\Training::create([
            'code'      => strtoupper($request->code),
            'name'      => $request->name,
            'refresh'   => $refresh,
            'mandatory' => $request->has('mandatory'),
            'audience'  => ['Lawyer', 'Paralegal', 'Hub Manager', 'M&E', 'Admin'],
        ]);

        return back()->with('success', "Training course {$request->code} added.");
    }

    public function deleteTraining(\App\Models\Training $training)
    {
        $training->delete();
        return back()->with('success', "Training course {$training->code} deleted.");
    }

    // ── Module Toggle ────────────────────────────────────────────

    public function toggleModule(string $key)
    {
        $settingKey = 'module_' . $key;
        $current = DB::table('settings')->where('key', $settingKey)->value('value');
        $newValue = ($current === 'off') ? 'on' : 'off';

        DB::table('settings')->updateOrInsert(
            ['key' => $settingKey],
            ['value' => $newValue, 'updated_at' => now(), 'created_at' => now()]
        );

        return back()->with('success', "Module updated.");
    }

    // ── Partner Organisation Management ─────────────────────────

    public function storePartnerCategory(Request $request)
    {
        $request->validate(['category' => 'required|string|max:100']);

        $label = trim($request->category);

        $exists = DB::table('lookups')
            ->where('group_key', 'partner_category')
            ->whereRaw('LOWER(label) = ?', [strtolower($label)])
            ->exists();

        if ($exists) {
            return back()->with('error', "Category '{$label}' already exists.");
        }

        $maxOrder = DB::table('lookups')->where('group_key', 'partner_category')->max('sort_order') ?? 0;

        DB::table('lookups')->insert([
            'group_key'  => 'partner_category',
            'value'      => $label,
            'label'      => $label,
            'sort_order' => $maxOrder + 1,
            'is_active'  => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', "Category '{$label}' added.");
    }

    public function storePartner(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'category'     => 'required|string',
            'focal_person' => 'nullable|string|max:255',
            'type'         => 'nullable|string|max:100',
            'mou_expires'  => 'nullable|date',
        ]);

        $lastId = \App\Models\Partner::orderByRaw("CAST(SUBSTRING(id, 3) AS UNSIGNED) DESC")->value('id');
        $nextNum = $lastId ? ((int) substr($lastId, 2)) + 1 : 1;
        $newId = 'P-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        \App\Models\Partner::create([
            'id'           => $newId,
            'name'         => $request->name,
            'category'     => $request->category,
            'focal_person' => $request->focal_person,
            'type'         => $request->type,
            'mou_expires'  => $request->mou_expires ?: null,
            'mou_status'   => $request->mou_expires ? 'active' : null,
        ]);

        return back()->with('success', "Partner '{$request->name}' added.");
    }

    public function updatePartner(Request $request, \App\Models\Partner $partner)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'category'     => 'required|string',
            'focal_person' => 'nullable|string|max:255',
            'type'         => 'nullable|string|max:100',
            'mou_expires'  => 'nullable|date',
        ]);

        $partner->update([
            'name'         => $request->name,
            'category'     => $request->category,
            'focal_person' => $request->focal_person,
            'type'         => $request->type,
            'mou_expires'  => $request->mou_expires ?: null,
            'mou_status'   => $request->mou_expires ? 'active' : null,
        ]);

        return back()->with('success', "Partner '{$partner->name}' updated.");
    }

    public function destroyPartner(\App\Models\Partner $partner)
    {
        $name = $partner->name;
        $partner->delete();
        return back()->with('success', "Partner '{$name}' removed.");
    }

    // ── Location Management ─────────────────────────────────────

    public function locationDetails(Request $request)
    {
        $district = $request->input('district');
        $rows = DB::table('locations')
            ->where('district', $district)
            ->orderBy('taluka')
            ->orderBy('union_council')
            ->get(['id', 'district', 'taluka', 'union_council', 'hub_id']);

        return response()->json($rows);
    }

    public function storeLocation(Request $request)
    {
        $data = $request->validate([
            'district'      => 'required|string|max:100',
            'taluka'        => 'nullable|string|max:100',
            'union_council' => 'nullable|string|max:200',
        ]);

        // Auto-assign hub_id if district matches a hub
        $hubId = Hub::where('district', $data['district'])->value('id');

        DB::table('locations')->insert([
            'province'      => 'Sindh',
            'district'      => $data['district'],
            'taluka'        => $data['taluka'] ?: null,
            'union_council' => $data['union_council'] ?: null,
            'hub_id'        => $hubId,
        ]);

        return back()->with('success', "Location added: {$data['district']}".($data['taluka'] ? " / {$data['taluka']}" : '').($data['union_council'] ? " / {$data['union_council']}" : ''));
    }

    public function deleteLocation(Request $request, $id)
    {
        DB::table('locations')->where('id', $id)->delete();
        return back()->with('success', 'Location deleted.');
    }

    public function bulkDeleteLocations(Request $request)
    {
        $data = $request->validate([
            'district' => 'required|string',
            'taluka'   => 'nullable|string',
        ]);

        $q = DB::table('locations')->where('district', $data['district']);
        if ($request->filled('taluka')) {
            $q->where('taluka', $data['taluka']);
        }
        $count = $q->delete();

        return back()->with('success', "Deleted {$count} location(s).");
    }

    // ── Cache Management ────────────────────────────────────────

    public function toggleCache()
    {
        $current = DB::table('settings')->where('key', 'cache_enabled')->value('value') ?? 'on';
        $newValue = ($current === 'off') ? 'on' : 'off';

        DB::table('settings')->updateOrInsert(
            ['key' => 'cache_enabled'],
            ['value' => $newValue, 'updated_at' => now(), 'created_at' => now()]
        );

        // Clear the meta-cache so the toggle takes effect immediately
        Cache::forget('jh.settings.cache_enabled');

        return back()->with('success', 'Dashboard cache ' . ($newValue === 'on' ? 'enabled' : 'disabled') . '.');
    }

    public function updateCacheTtl(Request $request)
    {
        $request->validate(['ttl' => 'required|in:120,300,600,900,1800']);

        DB::table('settings')->updateOrInsert(
            ['key' => 'cache_ttl'],
            ['value' => $request->ttl, 'updated_at' => now(), 'created_at' => now()]
        );

        Cache::forget('jh.settings.cache_ttl');

        $minutes = (int)$request->ttl / 60;
        return back()->with('success', "Cache duration set to {$minutes} minutes.");
    }

    public function flushCache()
    {
        DashboardMetricsService::flush();
        Cache::forget('jh.settings.cache_enabled');
        Cache::forget('jh.settings.cache_ttl');

        return back()->with('success', 'All dashboard caches cleared.');
    }

    // ── SLA Threshold Management ────────────────────────────────

    public function updateSla(Request $request)
    {
        $request->validate([
            'sla_Immediate' => 'required|integer|min:1|max:8760',
            'sla_High'      => 'required|integer|min:1|max:8760',
            'sla_Medium'    => 'required|integer|min:1|max:8760',
            'sla_Low'       => 'required|integer|min:1|max:8760',
        ]);

        $hours = [
            'Immediate' => (int) $request->sla_Immediate,
            'High'     => (int) $request->sla_High,
            'Medium'   => (int) $request->sla_Medium,
            'Low'      => (int) $request->sla_Low,
        ];

        DB::table('settings')->updateOrInsert(
            ['key' => 'sla_urgency_hours'],
            ['value' => json_encode($hours), 'updated_at' => now(), 'created_at' => now()]
        );

        return back()->with('success', 'SLA thresholds updated.');
    }

    // ── Workload Capacity Management ────────────────────────────

    public function updateCapacity(Request $request)
    {
        $request->validate([
            'cap_Lawyer'          => 'required|integer|min:1|max:200',
            'cap_HubCoordinator'  => 'required|integer|min:1|max:200',
        ]);

        $capacity = [
            'Lawyer'          => (int) $request->cap_Lawyer,
            'Hub Coordinator' => (int) $request->cap_HubCoordinator,
        ];

        DB::table('settings')->updateOrInsert(
            ['key' => 'workload_capacity'],
            ['value' => json_encode($capacity), 'updated_at' => now(), 'created_at' => now()]
        );

        return back()->with('success', 'Staff capacity limits updated.');
    }
}
