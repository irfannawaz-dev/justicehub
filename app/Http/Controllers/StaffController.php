<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $staff = Staff::forAuthUser()
            ->with(['trainings', 'hub', 'user'])
            ->get()
            ->sortBy(['hub_id', 'name']);

        $trainings = Training::orderBy('mandatory', 'desc')->orderBy('code')->get();
        $mandatoryTrainings = $trainings->where('mandatory', true);
        $today = now();

        // Annotate each staff member with compliance info
        $staff = $staff->map(function ($s) use ($mandatoryTrainings, $today) {
            $required = $mandatoryTrainings->filter(fn($t) => in_array($s->role, $t->audience ?? []));

            $trainingStatus = $s->trainings->keyBy('code')->map(function ($t) use ($today) {
                $expires = $t->pivot->expires ? Carbon::parse($t->pivot->expires) : null;
                if (!$expires) return 'current';
                if ($expires->lt($today)) return 'expired';
                if ($expires->lt($today->copy()->addMonths(3))) return 'expiring';
                return 'current';
            });

            $compliant = $required->every(function ($req) use ($s, $today) {
                $pivot = $s->trainings->where('code', $req->code)->first();
                if (!$pivot) return false;
                if ($req->refresh === 'one-off' || !$pivot->pivot->expires) return true;
                return Carbon::parse($pivot->pivot->expires)->gte($today);
            });

            $mandatoryDone = $required->filter(function ($req) use ($s, $today) {
                $pivot = $s->trainings->where('code', $req->code)->first();
                if (!$pivot) return false;
                if ($req->refresh === 'one-off' || !$pivot->pivot->expires) return true;
                return Carbon::parse($pivot->pivot->expires)->gte($today);
            })->count();

            $s->is_compliant = $compliant;
            $s->compliance_pct = $required->count() > 0 ? round(($mandatoryDone / $required->count()) * 100) : 100;
            $s->required_trainings = $required;
            $s->training_status = $trainingStatus;
            return $s;
        });

        $compliantCount  = $staff->where('is_compliant', true)->count();
        $compliancePct   = $staff->count() > 0 ? round(($compliantCount / $staff->count()) * 100) : 100;

        // Expiring soon (next 30 days)
        $expiring = $staff->flatMap(function ($s) use ($today) {
            return $s->trainings->filter(function ($t) use ($today) {
                if (!$t->pivot->expires) return false;
                $exp = Carbon::parse($t->pivot->expires);
                return $exp->gte($today) && $exp->lt($today->copy()->addDays(30));
            })->map(fn($t) => ['staff' => $s->name, 'code' => $t->code, 'expires' => $t->pivot->expires]);
        })->values();

        $grouped = $staff->groupBy('hub_id');

        return view('staff.index', compact('staff', 'trainings', 'mandatoryTrainings', 'grouped', 'compliancePct', 'compliantCount', 'expiring'));
    }

    public function logTraining(Request $request, Staff $staff)
    {
        abort_unless($request->user()->can('staff.training.log'), 403, 'You do not have permission to log training.');

        $request->validate([
            'training_code' => 'required|string|exists:trainings,code',
            'completed_on'  => 'required|date|before_or_equal:today',
            'delivered_by'  => 'required|string|max:100',
            'expires'       => 'nullable|date|after:completed_on',
        ]);

        $training = Training::where('code', $request->training_code)->firstOrFail();

        // Calculate expiry
        $expires = null;
        if ($request->filled('expires')) {
            $expires = $request->expires;
        } elseif ($training->refresh !== 'one-off') {
            $months = config("justice_hub.training_expiry.{$training->refresh}", 12);
            if ($months) {
                $expires = Carbon::parse($request->completed_on)->addMonths($months)->toDateString();
            }
        }

        // Detach existing record for this training if exists
        $staff->trainings()->detach($training->id);

        // Attach updated record
        $staff->trainings()->attach($training->id, [
            'completed_on' => $request->completed_on,
            'expires'      => $expires,
            'delivered_by' => $request->delivered_by,
        ]);

        return back()->with('success', "{$training->name} logged for {$staff->name}.");
    }

    public function logUserTraining(Request $request, \App\Models\User $user)
    {
        abort_unless($request->user()->can('staff.training.log'), 403);

        $request->validate([
            'training_code' => 'required|string|exists:trainings,code',
            'completed_on'  => 'required|date',
            'delivered_by'  => 'required|string|max:100',
            'expires'       => 'nullable|date',
        ]);

        $training = Training::where('code', $request->training_code)->firstOrFail();

        // Calculate expiry from refresh period
        $expires = $request->expires;
        if (!$expires && $training->refresh && $training->refresh !== 'one-off') {
            $val = (int) preg_replace('/[^0-9]/', '', $training->refresh);
            $unit = str_contains($training->refresh, 'd') ? 'days' : 'months';
            $expires = Carbon::parse($request->completed_on)->add($unit, $val)->toDateString();
        }

        // Find or create a staff record for this user
        $staffHubId = $user->hub_id ?: \App\Models\Hub::where('is_active', true)->first()?->id ?? 'JH-HYD-01';
        $staff = Staff::firstOrCreate(
            ['name' => $user->name],
            [
                'staff_uid'   => 'STF-' . str_pad((Staff::max('id') ?? 0) + 1, 3, '0', STR_PAD_LEFT),
                'hub_id'      => $staffHubId,
                'role'        => $user->role->label(),
                'initials'    => collect(explode(' ', $user->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->join(''),
                'status'      => 'active',
                'joined_date' => $user->created_at?->toDateString() ?? now()->toDateString(),
            ]
        );

        // Detach existing then attach
        $staff->trainings()->detach($training->id);
        $staff->trainings()->attach($training->id, [
            'completed_on' => $request->completed_on,
            'expires'      => $expires,
            'delivered_by' => $request->delivered_by,
        ]);

        return back()->with('success', "{$training->name} logged for {$user->name}.");
    }
}
