<?php

namespace App\Http\Controllers;

use App\Enums\EvidenceType;
use App\Models\Evidence;
use App\Models\Indicator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EvidenceController extends Controller
{
    public function index(Request $request)
    {
        $query = Evidence::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('verified')) {
            $query->where('verified', $request->verified === '1');
        }
        if ($request->filled('hub')) {
            $query->where(fn($q) => $q->where('hub_id', $request->hub)->orWhereNull('hub_id'));
        }

        $evidence = $query->orderByDesc('date')->paginate(20)->withQueryString();

        $counts = [
            'total'      => Evidence::count(),
            'verified'   => Evidence::where('verified', true)->count(),
            'pending'    => Evidence::where('verified', false)->count(),
            'by_type'    => Evidence::selectRaw('type, count(*) as cnt')->groupBy('type')->pluck('cnt', 'type'),
        ];

        $indicators = Indicator::orderBy('code')->pluck('code');
        $types = ['recognition', 'integration', 'replication', 'policy-citation', 'analytical-product'];

        return view('evidence.index', compact('evidence', 'counts', 'indicators', 'types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'     => ['required', Rule::in(['recognition', 'integration', 'replication', 'policy-citation', 'analytical-product'])],
            'title'    => 'required|string|max:255',
            'summary'  => 'required|string|max:3000',
            'date'     => 'required|date|before_or_equal:today',
            'issuer'   => 'required|string|max:255',
            'hub_id'   => 'nullable|exists:hubs,id',
            'linked_indicator' => 'nullable|exists:indicators,code',
            'files'    => 'nullable|array|max:5',
            'files.*'  => 'file|max:51200|mimes:pdf,jpg,jpeg,png,mp4,mov,mp3,doc,docx',
        ]);

        $lastNum = Evidence::selectRaw("MAX(CAST(SUBSTRING(evidence_uid, 4) AS UNSIGNED)) as max_num")->value('max_num');
        $nextNum = $lastNum ? $lastNum + 1 : 21;
        $uid = 'EV-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        // Handle file uploads
        $uploadedFiles = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store("evidence/{$uid}", 'public');
                $uploadedFiles[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                ];
            }
        }

        Evidence::create([
            'evidence_uid'     => $uid,
            'type'             => $request->type,
            'title'            => $request->title,
            'summary'          => $request->summary,
            'date'             => $request->date,
            'issuer'           => $request->issuer,
            'hub_id'           => $request->hub_id ?: null,
            'document_ref'     => $request->document_ref,
            'tags'             => $request->tags ? array_map('trim', explode(',', $request->tags)) : [],
            'linked_indicator' => $request->linked_indicator ?: null,
            'verified'         => false,
            'meta'             => !empty($uploadedFiles) ? ['files' => $uploadedFiles] : null,
        ]);

        return back()->with('success', "Evidence {$uid} registered.");
    }

    public function verify(Request $request, Evidence $evidence)
    {
        abort_unless($request->user()->can('evidence.verify'), 403, 'Only M&E Lead and Head can verify evidence.');

        $evidence->update([
            'verified'      => true,
            'verified_by'   => auth()->user()->name,
            'verified_date' => now()->toDateString(),
        ]);

        return back()->with('success', 'Evidence verified.');
    }
}
