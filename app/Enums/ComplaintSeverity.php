<?php

namespace App\Enums;

enum ComplaintSeverity: string
{
    case Critical = 'critical';
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';

    public function slaDays(): int
    {
        return match ($this) {
            self::Critical => 3,
            self::High     => 7,
            self::Medium   => 14,
            self::Low      => 30,
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Critical => 'var(--burgundy)',
            self::High     => 'var(--ochre)',
            self::Medium   => 'var(--forest)',
            self::Low      => 'var(--ink-2)',
        };
    }

    public function tint(): string
    {
        return match ($this) {
            self::Critical => 'var(--burgundy-tint)',
            self::High     => 'var(--ochre-tint)',
            self::Medium   => 'rgba(22,48,41,0.08)',
            self::Low      => 'var(--parchment-2)',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Critical => 'Critical',
            self::High     => 'High',
            self::Medium   => 'Medium',
            self::Low      => 'Low',
        };
    }
}
