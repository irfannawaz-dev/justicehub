<?php

namespace App\Enums;

enum EvidenceType: string
{
    case Recognition = 'recognition';
    case Integration = 'integration';
    case Replication = 'replication';
    case PolicyCitation = 'policy-citation';
    case AnalyticalProduct = 'analytical-product';

    public function label(): string
    {
        return match ($this) {
            self::Recognition      => 'Recognition',
            self::Integration      => 'Integration',
            self::Replication      => 'Replication',
            self::PolicyCitation   => 'Policy Citation',
            self::AnalyticalProduct => 'Analytical Product',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Recognition      => 'var(--forest)',
            self::Integration      => 'var(--moss)',
            self::Replication      => 'var(--ochre)',
            self::PolicyCitation   => 'var(--ink-2)',
            self::AnalyticalProduct => 'var(--forest-3)',
        };
    }
}
