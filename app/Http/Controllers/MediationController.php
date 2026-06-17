<?php

namespace App\Http\Controllers;

use App\Models\CaseRecord;
use App\Models\MediationParty;
use App\Models\MediationDiary;
use Illuminate\Http\Request;

class MediationController extends Controller
{
    // Step 1 — Add a party
    public function storeParty(Request $request, CaseRecord $case)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'role'  => 'required|string',
            'phone' => 'nullable|string|max:30',
            'note'  => 'nullable|string|max:500',
        ]);

        MediationParty::create([
            'case_id'        => $case->id,
            'name'           => $request->name,
            'role'           => $request->role,
            'phone'          => $request->phone,
            'note'           => $request->note,
            'consent_status' => 'awaiting',
        ]);

        return redirect()->route('cases.show', $case)->with('mstep', 1)->withFragment('tab-referrals')
            ->with('flash_mstep', 1);
    }

    // Step 1 — Remove a party
    public function destroyParty(CaseRecord $case, MediationParty $party)
    {
        abort_if($party->case_id !== $case->id, 403);
        $party->delete();

        return redirect()->route('cases.show', $case)->withFragment('tab-referrals')
            ->with('flash_mstep', 1);
    }

    // Step 2 — Update consent for all parties at once
    public function updateConsent(Request $request, CaseRecord $case)
    {
        $statuses = $request->input('consent', []);

        foreach ($statuses as $partyId => $status) {
            if (in_array($status, ['awaiting', 'agreed', 'declined'])) {
                MediationParty::where('id', $partyId)
                    ->where('case_id', $case->id)
                    ->update(['consent_status' => $status]);
            }
        }

        $anyAgreed = $case->mediationParties()->where('consent_status', 'agreed')->exists();
        return redirect()->route('cases.show', $case)->withFragment('tab-referrals')
            ->with('flash_mstep', $anyAgreed ? 3 : 2);
    }

    // Step 3 — Add diary entry
    public function storeDiary(Request $request, CaseRecord $case)
    {
        $request->validate([
            'session_date'          => 'required|date',
            'next_session_date'     => 'nullable|date',
            'what_happened'         => 'required|string',
            'note_for_next_session' => 'nullable|string|max:1000',
        ]);

        MediationDiary::create([
            'case_id'               => $case->id,
            'session_date'          => $request->session_date,
            'next_session_date'     => $request->next_session_date,
            'what_happened'         => $request->what_happened,
            'note_for_next_session' => $request->note_for_next_session,
            'logged_by'             => auth()->user()->name,
        ]);

        return redirect()->route('cases.show', $case)->withFragment('tab-referrals')
            ->with('flash_mstep', 3);
    }
}
