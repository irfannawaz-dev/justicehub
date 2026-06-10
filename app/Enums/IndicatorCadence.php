<?php

namespace App\Enums;

enum IndicatorCadence: string
{
    case Monthly = 'Monthly';
    case Quarterly = 'Quarterly';
    case Annual = 'Annual';

    public function label(): string
    {
        return $this->value;
    }

    public function months(): int
    {
        return match ($this) {
            self::Monthly   => 1,
            self::Quarterly => 3,
            self::Annual    => 12,
        };
    }
}
