<?php

namespace App\Enums;

enum CaseDisposition: string
{
    case Adr = 'adr';
    case Litigation = 'litigation';
    case AdviceOnly = 'advice-only';
    case Referred = 'referred';

    public function label(): string
    {
        return match ($this) {
            self::Adr        => 'ADR',
            self::Litigation => 'Litigation',
            self::AdviceOnly => 'Advice Only',
            self::Referred   => 'Referred',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Adr        => 'var(--moss)',
            self::Litigation => 'var(--forest)',
            self::AdviceOnly => 'var(--ink-3)',
            self::Referred   => 'var(--ochre)',
        };
    }
}
