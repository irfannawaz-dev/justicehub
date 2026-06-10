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

        $results = CaseRecord::query()
            ->forHub($request->input('_active_hub'))
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
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
