<?php

namespace App\Enums;

enum RiskLevel: string
{
    case High = 'High';
    case Medium = 'Medium';
    case Low = 'Low';

    public function color(): string
    {
        return match ($this) {
            self::High   => 'var(--burgundy)',
            self::Medium => 'var(--ochre)',
            self::Low    => 'var(--forest)',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::High   => 'burgundy',
            self::Medium => 'ochre',
            self::Low    => 'forest',
        };
    }
}
