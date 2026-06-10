<?php

namespace App\Enums;

enum CaseStatus: string
{
    case Active = 'Active';
    case PendingApproval = 'Pending Approval';
    case Settlement = 'Settlement';
    case Closed = 'Closed';
    case Rejected = 'Rejected';

    public function color(): string
    {
        return match ($this) {
            self::Active          => 'var(--forest)',
            self::PendingApproval => 'var(--ochre)',
            self::Settlement      => 'var(--moss)',
            self::Closed          => 'var(--ink-3)',
            self::Rejected        => 'var(--burgundy)',
        };
    }

    public function label(): string
    {
        return $this->value;
    }

    public function badge(): string
    {
        return match ($this) {
            self::Active          => 'forest',
            self::PendingApproval => 'ochre',
            self::Settlement      => 'moss',
            self::Closed          => 'muted',
            self::Rejected        => 'burgundy',
        };
    }
}
