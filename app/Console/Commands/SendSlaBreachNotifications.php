<?php

namespace App\Console\Commands;

use App\Models\CaseRecord;
use App\Models\User;
use App\Notifications\CaseNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class SendSlaBreachNotifications extends Command
{
    protected $signature   = 'notifications:sla-check';
    protected $description = 'Notify assigned users of cases approaching or in SLA breach';

    public function handle(): void
    {
        $now = now();

        // Active cases only — Closed / Settlement don't need SLA alerts
        $cases = CaseRecord::whereNotIn('status', ['Closed', 'Settlement'])
            ->whereNull('deleted_at')
            ->get();

        $slaHours = config('justice_hub.sla.urgency_hours');

        foreach ($cases as $case) {
            $hours    = $slaHours[$case->urgency->value] ?? 168;
            $intakeDt = \Carbon\Carbon::parse(
                $case->intake_date->toDateString() . ' ' . ($case->intake_time ?? '00:00')
            );
            $deadline = $intakeDt->copy()->addHours($hours);

            // Already a recorded breach — skip
            if ($case->sla_met === false) continue;

            $hoursLeft = $now->diffInHours($deadline, false); // negative = past deadline

            // Notify: approaching (within 4 hours) or just breached (0 to -2 hours window)
            $isApproaching = $hoursLeft > 0 && $hoursLeft <= 4;
            $isJustBreached = $hoursLeft <= 0 && $hoursLeft >= -2;

            if (! $isApproaching && ! $isJustBreached) continue;

            $assignedUser = User::where('name', $case->assigned_to)
                ->where('hub_id', $case->hub_id)
                ->first();

            if (! $assignedUser) continue;

            // Avoid duplicate alerts — check if one was sent in last 3 hours
            $alreadyNotified = $assignedUser->notifications()
                ->where('type', CaseNotification::class)
                ->where('created_at', '>=', $now->copy()->subHours(3))
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.action_url')) LIKE ?", [
                    '%/cases/' . $case->id
                ])
                ->exists();

            if ($alreadyNotified) continue;

            $label   = $isApproaching ? "SLA deadline in {$hoursLeft}h" : 'SLA deadline passed';
            $caseUrl = url('/cases/' . $case->id);

            $assignedUser->notify(new CaseNotification(
                title:      "⚠ SLA Alert — {$case->case_uid}",
                message:    "{$label} for case {$case->case_uid} ({$case->name}). Urgency: {$case->urgency->value}. Deadline: {$deadline->format('M d, Y H:i')}.",
                actionText: 'View Case',
                actionUrl:  $caseUrl,
                type:       'sla',
            ));

            $this->line("Notified {$assignedUser->name} — {$case->case_uid} ({$label})");
        }

        $this->info('SLA check complete.');
    }
}
