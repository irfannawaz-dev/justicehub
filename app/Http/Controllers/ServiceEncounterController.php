<?php

namespace App\Http\Controllers;

use App\Models\CaseRecord;
use App\Models\ServiceEncounter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceEncounterController extends Controller
{
    public function store(Request $request, CaseRecord $case)
    {
        $request->validate([
            'date' => 'required|date',
            'type' => 'required|string',
            'performed_by' => 'required|string',
            'note' => 'required|string',
        ]);

        ServiceEncounter::create([
            'case_id'      => $case->id,
            'date'         => $request->date,
            'type'         => $request->type,
            'performed_by' => $request->performed_by,
            'note'         => $request->note,
        ]);

        $firstEncounter = ServiceEncounter::where('case_id', $case->id)->orderBy('date')->value('date');
        $sla = $case->computeSlaStatus($firstEncounter);
        $case->update([
            'last_update' => now()->toDateString(),
            'sla_met'     => $sla['status'] === 'met',
        ]);

        // Notify assigned user (skip if the person logging IS the assigned user)
        if (($assignedUser = $case->getAssignedUser()) && $assignedUser->id !== auth()->id()) {
            $assignedUser->notify(new \App\Notifications\CaseNotification(
                title:      "Case updated — {$case->case_uid}",
                message:    "A service encounter was logged for case {$case->case_uid} ({$case->name}) by {$request->performed_by}.",
                actionText: 'View Case',
                actionUrl:  route('cases.show', $case),
                type:       'updated',
            ));
        }

        return back()->with('success', 'Service encounter logged.');
    }

    // ─────────────────────────────────────────────────────────────
    // Log a service encounter from the ADR / Litigation scorecard
    // (case_id comes from form body, not URL)
    // ─────────────────────────────────────────────────────────────

    public function logFromScorecard(Request $request)
    {
        $validated = $request->validate([
            'case_id'        => 'required|exists:cases,id',
            'type'           => 'required|string|max:100',
            'date'           => 'required|date',
            'time'           => 'nullable|string|max:20',
            'performed_by'   => 'required|string|max:150',
            'duration'       => 'nullable|string|max:50',
            'mode'           => ['nullable', Rule::in(['In-person', 'Phone', 'Field'])],
            'outcome'        => 'nullable|string|max:80',
            'court'          => 'nullable|string|max:200',
            'note'           => 'nullable|string|max:3000',
            'next_step'      => 'nullable|string|max:500',
            'next_step_date' => 'nullable|date',
        ]);

        $case = CaseRecord::findOrFail($validated['case_id']);

        ServiceEncounter::create([
            'case_id'      => $validated['case_id'],
            'date'         => $validated['date'],
            'type'         => $validated['type'],
            'performed_by' => $validated['performed_by'],
            'note'         => $validated['note'] ?? '',
            'meta'         => array_filter([
                'time'           => $validated['time'] ?? null,
                'duration'       => $validated['duration'] ?? null,
                'mode'           => $validated['mode'] ?? null,
                'outcome'        => $validated['outcome'] ?? null,
                'court'          => $validated['court'] ?? null,
                'next_step'      => $validated['next_step'] ?? null,
                'next_step_date' => $validated['next_step_date'] ?? null,
            ]),
        ]);

        $firstEncounter2 = ServiceEncounter::where('case_id', $case->id)->orderBy('date')->value('date');
        $sla2 = $case->computeSlaStatus($firstEncounter2);
        $case->update([
            'last_update' => now()->toDateString(),
            'sla_met'     => $sla2['status'] === 'met',
        ]);

        // Advance case status on terminal outcomes
        $outcome = $validated['outcome'] ?? '';
        if (!in_array($case->status->value, ['Closed', 'Settlement'])) {
            if ($outcome === 'Resolved' || $outcome === 'Won' || $outcome === 'Partial') {
                $case->update(['status' => 'Settlement']);
            } elseif ($outcome === 'Lost') {
                $case->update(['status' => 'Closed']);
            }
        }

        return back()->with('success', "Encounter logged for case {$case->case_uid}.");
    }
}
