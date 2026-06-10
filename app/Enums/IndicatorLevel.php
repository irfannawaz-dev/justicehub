<?php

namespace App\Enums;

enum IndicatorLevel: string
{
    case Goal = 'Goal';
    case Outcome1 = 'Outcome 1';
    case Outcome2 = 'Outcome 2';
    case Outcome3 = 'Outcome 3';
    case Output1 = 'Output 1';
    case Output2 = 'Output 2';
    case Output3 = 'Output 3';
    case Output4 = 'Output 4';

    public function label(): string
    {
        return $this->value;
    }

    public function color(): string
    {
        return match ($this) {
            self::Goal     => 'var(--forest)',
            self::Outcome1,
            self::Outcome2,
            self::Outcome3 => 'var(--ochre)',
            self::Output1,
            self::Output2,
            self::Output3,
            self::Output4  => 'var(--forest-3)',
        };
    }

    public function shortCode(): string
    {
        return match ($this) {
            self::Goal     => 'G',
            self::Outcome1 => 'O1',
            self::Outcome2 => 'O2',
            self::Outcome3 => 'O3',
            self::Output1  => 'OP1',
            self::Output2  => 'OP2',
            self::Output3  => 'OP3',
            self::Output4  => 'OP4',
        };
    }
}
