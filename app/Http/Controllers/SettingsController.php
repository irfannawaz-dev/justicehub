<?php

namespace App\Http\Controllers;

use App\Models\FinanceConfig;
use App\Models\Hub;
use App\Models\Lookup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $lookupData = null;
        if ($request->user()->can('lookups.manage')) {
            $lookupData = Lookup::orderBy('group_key')
                ->orderBy('sort_order')
                ->get()
                ->groupBy('group_key');
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

        return view('settings.index', compact('lookupData', 'locationData'));
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
}
