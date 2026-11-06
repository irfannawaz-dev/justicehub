<?php

namespace App\Policies;

use App\Enums\CaseDisposition;
use App\Models\CaseRecord;
use App\Models\User;

class CaseRecordPolicy
{
    private const LITIGATION_PATHWAYS = [
        'Representation in Court',
        'Court Representation',
    ];

    private const ADR_PATHWAYS = [
        'Mediation',
        'ADR / Dispute Resolution Support',
    ];

    public function view(User $user, CaseRecord $case): bool
    {
        if (! $user->can('cases.view')) {
            return false;
        }

        if (! $user->canSeeAllHubs() && $case->hub_id !== $user->hub_id) {
            return false;
        }

        if ($user->isLawyer()) {
            return $this->isAssignedTo($user, $case)
                && ! in_array($case->assigned_pathway, self::ADR_PATHWAYS, true);
        }

        if ($user->isCourtClerk()) {
            return $case->disposition === CaseDisposition::Litigation
                || in_array($case->assigned_pathway, self::LITIGATION_PATHWAYS, true);
        }

        if ($user->isLitigationManager()) {
            return in_array($case->assigned_pathway, self::LITIGATION_PATHWAYS, true);
        }

        if ($user->isMediationManager()) {
            return $case->assigned_pathway === 'Mediation';
        }

        return true;
    }

    public function update(User $user, CaseRecord $case): bool
    {
        return $user->can('cases.edit') && $this->view($user, $case);
    }

    private function isAssignedTo(User $user, CaseRecord $case): bool
    {
        if ($case->assigned_staff_id !== null) {
            return $case->assignedStaff()
                ->where('user_id', $user->id)
                ->exists();
        }

        if ($case->assigned_to === null || ! hash_equals($case->assigned_to, $user->name)) {
            return false;
        }

        return ! User::query()
            ->where('hub_id', $case->hub_id)
            ->where('name', $case->assigned_to)
            ->whereKeyNot($user->id)
            ->exists();
    }
}
