<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CaseRecord;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->input('q', '');
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $user  = $request->user();
        $hubId = $user->canSeeAllHubs()
            ? $request->input('_active_hub')
            : $user->hub_id;

        $query = CaseRecord::query()->forHub($hubId);

        // Lawyers only see their own assigned cases
        if ($user->isLawyer()) {
            $query->where('assigned_to', $user->name)
                  ->whereNotIn('assigned_pathway', ['Mediation', 'ADR / Dispute Resolution Support']);
        }

        $results = $query->where(function ($q2) use ($q) {
                $q2->where('name', 'like', "%{$q}%")
                   ->orWhere('case_uid', 'like', "%{$q}%")
                   ->orWhere('case_ref', 'like', "%{$q}%")
                   ->orWhere('primary_issue', 'like', "%{$q}%")
                   ->orWhere('district', 'like', "%{$q}%");
            })
            ->select('id', 'case_uid', 'name', 'primary_issue', 'status', 'hub_id')
            ->limit(8)
            ->get();

        return response()->json($results);
    }
}
