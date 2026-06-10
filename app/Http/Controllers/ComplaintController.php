<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\ComplaintAction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $hubId = $request->input('_active_hub', 'all');

        $complaints = Complaint::with(['caseRecord:id,case_uid,hub_id', 'hub:id,name'])
            ->when($hubId && $hubId !== 'all', fn($q) => $q->where('hub_id', $hubId))
            ->orderByDesc('submitted_date')
            ->get();

        $resolved     = $complaints->where('status', 'resolved');
        $open         = $complaints->whereNotIn('status', ['resolved']);
        $resolvedCount = $resolved->count();

        // OP4.3 – resolved within SLA
        $withinSla = $resolved->filter(function ($c) {
            if (!$c->resolved_date) return false;
            $daysToResolve = $c->submitted_date->diffInDays($c->resolved_date);
            return $daysToResolve <= $c->sla_days;
        })->count();

        $slaRate = $resolvedCount > 0 ? round(($withinSla / $resolvedCount) * 100) : 0;

        // Avg resolution days
        $avgResolutionDays = $resolvedCount > 0
            ? round($resolved->filter(fn ($c) => $c->resolved_date)
                ->avg(fn ($c) => $c->submitted_date->diffInDays($c->resolved_date)), 1)
            : 0;

        // Currently open (open + in-progress + escalated)
        $currentlyOpen = $open->count();
        $openWithinSla = $open->filter(fn ($c) => !$c->isOverdue())->count();

        // Severity bars
        $severities = ['critical' => 3, 'high' => 7, 'medium' => 14, 'low' => 30];
        $severityBars = collect($severities)->map(function ($sla, $sev) use ($complaints) {
            return [
                'label'    => ucfirst($sev),
                'sla'      => $sla,
                'open'     => $complaints->whereNotIn('status', ['resolved'])->where('severity', $sev)->count(),
                'resolved' => $complaints->where('status', 'resolved')->where('severity', $sev)->count(),
            ];
        });
        $maxSeverityTotal = $severityBars->max(fn ($s) => $s['open'] + $s['resolved']) ?: 1;

        // Category breakdown
        $categoryStats = $complaints->groupBy('category')
            ->map(fn ($grp) => $grp->count())
            ->sortByDesc(fn ($v) => $v)
            ->take(8);
        $maxCategoryCount = $categoryStats->max() ?: 1;

        // Counts for filter pills
        $counts = [
            'total'    => $complaints->count(),
            'open'     => $open->count(),
            'resolved' => $resolvedCount,
            'overdue'  => $complaints->filter(fn ($c) => $c->isOverdue())->count(),
        ];

        return view('complaints.index', compact(
            'complaints', 'counts',
            'slaRate', 'withinSla', 'resolvedCount',
            'avgResolutionDays', 'currentlyOpen', 'openWithinSla',
            'severityBars', 'maxSeverityTotal',
            'categoryStats', 'maxCategoryCount',
        ));
    }

    public function show(Complaint $complaint)
    {
        // Hub scope enforced via Route::bind() in AppServiceProvider
        $complaint->load(['caseRecord', 'actions']);
        return view('complaints.show', compact('complaint'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'description'  => 'required|string|max:2000',
            'category'     => 'required|string',
            'severity'     => ['required', Rule::in(['critical', 'high', 'medium', 'low'])],
            'submitted_by' => 'required|string|max:150',
            'channel'      => ['nullable', Rule::in(['in-person', 'phone', 'written', 'digital'])],
        ]);

        $lastUid = Complaint::selectRaw("MAX(CAST(SUBSTRING(complaint_uid, 5) AS UNSIGNED)) as max_num")->value('max_num');
        $nextNum = $lastUid ? $lastUid + 1 : 100;

        $severity = $request->severity;
        $slaDays  = config("justice_hub.complaint_sla_days.{$severity}", 14);

        $complaint = Complaint::create([
            'complaint_uid'  => 'CMP-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT),
            'case_id'        => $request->case_id ?: null,
            'submitted_date' => now()->toDateString(),
            'submitted_by'   => $request->submitted_by,
            'is_anonymous'   => $request->has('is_anonymous'),
            'channel'        => $request->channel ?? 'in-person',
            'category'       => $request->category,
            'severity'       => $severity,
            'sla_days'       => $slaDays,
            'description'    => $request->description,
            'hub_id'         => auth()->user()->canSeeAllHubs()
                                    ? ($request->hub_id ?? auth()->user()->hub_id)
                                    : auth()->user()->hub_id,
            'assigned_to'    => $request->assigned_to,
            'status'         => 'open',
        ]);

        ComplaintAction::create([
            'complaint_id' => $complaint->id,
            'date'         => now()->toDateString(),
            'performed_by' => auth()->user()->name,
            'note'         => 'Complaint received and registered. Severity: ' . strtoupper($severity) . '. SLA: ' . $slaDays . ' days.',
        ]);

        return back()->with('success', 'Complaint logged: ' . $complaint->complaint_uid);
    }

    public function addAction(Request $request, Complaint $complaint)
    {
        abort_unless($request->user()->can('complaints.resolve'), 403, 'You do not have permission to action complaints.');

        $request->validate([
            'note'       => 'required|string|max:2000',
            'new_status' => ['nullable', Rule::in(['open', 'in-progress', 'resolved', 'escalated'])],
        ]);

        ComplaintAction::create([
            'complaint_id' => $complaint->id,
            'date'         => now()->toDateString(),
            'performed_by' => auth()->user()->name,
            'note'         => $request->note,
        ]);

        if ($request->filled('new_status')) {
            $complaint->update([
                'status'        => $request->new_status,
                'resolved_date' => $request->new_status === 'resolved' ? now()->toDateString() : null,
                'resolution'    => $request->new_status === 'resolved' ? $request->note : $complaint->resolution,
            ]);
        }

        return back()->with('success', 'Action logged.');
    }
}
