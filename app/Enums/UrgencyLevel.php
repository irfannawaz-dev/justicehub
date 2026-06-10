<?php

namespace App\Enums;

enum UrgencyLevel: string
{
    case Immediate = 'Immediate';
    case High = 'High';
    case Medium = 'Medium';
    case Low = 'Low';

    public function color(): string
    {
        return match ($this) {
            self::Immediate => 'var(--burgundy)',
            self::High     => 'var(--ochre)',
            self::Medium   => 'var(--forest)',
            self::Low      => 'var(--ink-3)',
        };
    }

    public function slaHours(): int
    {
        return match ($this) {
            self::Immediate => 4,
            self::High     => 24,
            self::Medium   => 72,
            self::Low      => 168,
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Immediate => 'burgundy',
            self::High     => 'ochre',
            self::Medium   => 'forest',
            self::Low      => 'muted',
        };
    }
}
