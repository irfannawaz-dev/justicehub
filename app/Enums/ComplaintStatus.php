<?php

namespace App\Enums;

enum ComplaintStatus: string
{
    case Open = 'open';
    case InProgress = 'in-progress';
    case Resolved = 'resolved';
    case Escalated = 'escalated';

    public function label(): string
    {
        return match ($this) {
            self::Open       => 'Open',
            self::InProgress => 'In Progress',
            self::Resolved   => 'Resolved',
            self::Escalated  => 'Escalated',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Open       => 'var(--ochre)',
            self::InProgress => 'var(--forest)',
            self::Resolved   => 'var(--moss)',
            self::Escalated  => 'var(--burgundy)',
        };
    }
}
