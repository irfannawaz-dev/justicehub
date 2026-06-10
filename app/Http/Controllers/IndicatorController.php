<?php

namespace App\Http\Controllers;

use App\Models\Evidence;
use App\Models\Indicator;
use App\Models\IndicatorSnapshot;
use App\Services\IndicatorDerivationService;
use Illuminate\Http\Request;

class IndicatorController extends Controller
{
    public function index()
    {
        $service = new IndicatorDerivationService();
        $actuals = $service->derive();

        $levelOrder = ['Goal', 'Outcome 1', 'Outcome 2', 'Outcome 3', 'Output 1', 'Output 2', 'Output 3', 'Output 4'];

        $indicators = Indicator::all()
            ->map(function ($ind) use ($actuals, $service) {
                $liveActual = $actuals[$ind->code] ?? null;
                if ($liveActual !== null) {
                    $ind->actual = $liveActual;
                }
                $ind->source_line = $service->sourceLine($ind->code);
                $ind->rag = $ind->ragStatus();

                $snapshots = IndicatorSnapshot::where('indicator_code', $ind->code)
                    ->orderBy('month_iso')->get();
                $currentMonth = now()->format('M');
                $ind->trend_labels = $snapshots->pluck('month_label')->push($currentMonth)->values()->toArray();
                $ind->trend_values = $snapshots->pluck('value')->push((float)$ind->actual)->values()->toArray();

                // Linked evidence entries
                $ind->evidence = Evidence::where('linked_indicator', $ind->code)->get(['id', 'evidence_uid', 'title', 'issuer', 'verified']);

                // Compute % achieved
                $ind->pct = $ind->target > 0
                    ? ($ind->is_inverse
                        ? min(100, round(($ind->target / max($ind->actual, 0.01)) * 100))
                        : min(100, round(($ind->actual / $ind->target) * 100)))
                    : 100;

                return $ind;
            })
            ->sortBy(fn($ind) => [array_search($ind->level, $levelOrder), $ind->code]);

        $grouped = $indicators->groupBy('level');

        $counts = [
            'total'    => $indicators->count(),
            'green'    => $indicators->where('rag', 'green')->count(),
            'amber'    => $indicators->where('rag', 'amber')->count(),
            'red'      => $indicators->where('rag', 'red')->count(),
            'p0'       => $indicators->where('priority', 'P0')->count(),
            'on_track' => $indicators->whereIn('rag', ['green', 'amber'])->count(),
        ];

        // Build panel data keyed by indicator ID
        $panelData = $indicators->mapWithKeys(fn($ind) => [
            $ind->id => [
                'id'           => $ind->id,
                'code'         => $ind->code,
                'name'         => $ind->name,
                'level'        => $ind->level,
                'priority'     => $ind->priority,
                'cadence'      => ucfirst($ind->cadence ?? ''),
                'actual'       => $ind->actual,
                'target'       => $ind->target,
                'unit'         => $ind->unit,
                'pct'          => $ind->pct,
                'rag'          => $ind->rag,
                'source_line'  => $ind->source_line,
                'trend_labels' => $ind->trend_labels,
                'trend_values' => $ind->trend_values,
                'methodology'  => $ind->meta['methodology'] ?? $ind->name,
                'evidence'     => $ind->evidence->map(fn($e) => [
                    'uid'      => $e->evidence_uid,
                    'title'    => $e->title,
                    'issuer'   => $e->issuer,
                    'verified' => $e->verified,
                ])->values(),
            ]
        ]);

        return view('indicators.index', compact('indicators', 'grouped', 'counts', 'panelData'));
    }
}
