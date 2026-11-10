<?php

namespace App\Support;

use App\Models\CaseRecord;

final class LasCmsIdentity
{
    public static function normalizeCnic(?string $cnic): string
    {
        return preg_replace('/\D/', '', (string) $cnic);
    }

    public static function matches(CaseRecord $case, array $record): bool
    {
        $cnic = self::normalizeCnic($case->cnic);

        return strlen($cnic) === 13
            && hash_equals($cnic, self::normalizeCnic($record['cnic'] ?? null));
    }
}
