<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $hubId = $request->input('_active_hub', 'all');

        $query = Activity::with('causer')
            ->latest()
            ->when($request->input('search'), function ($q, $search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('description', 'like', "%{$search}%")
                       ->orWhere('properties', 'like', "%{$search}%");
                });
            })
            ->when($request->input('event'), fn($q, $event) => $q->where('event', $event))
            ->when($request->input('user'), fn($q, $userId) => $q->where('causer_id', $userId));

        // Hub filter: only show activities for cases belonging to selected hub
        if ($hubId && $hubId !== 'all') {
            $caseIds = \App\Models\CaseRecord::where('hub_id', $hubId)->pluck('id');
            $query->where('subject_type', \App\Models\CaseRecord::class)
                  ->whereIn('subject_id', $caseIds);
        }

        $activities = $query->paginate(50)->withQueryString();

        $users = \App\Models\User::orderBy('name')
            ->select('id', 'name')
            ->get();

        return view('activity-log.index', compact('activities', 'users'));
    }
}
