<?php

namespace App\Support;

use App\Models\CaseRecord;

final class LasCmsIdentity
{
    public static function normalizeCnic(?string $cnic): string
    {
        return preg_replace('/\D/', '', (string) $cnic);
    }

    public static function normalizeName(?string $name): string
    {
        $name = mb_strtolower(trim((string) $name), 'UTF-8');
        $name = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $name) ?? '';
        $parts = array_values(array_filter(explode(' ', trim($name))));
        $honorifics = ['mr', 'mrs', 'ms', 'miss', 'mst', 'mstt', 'smt', 'sh', 'sht', 'adv', 'advocate'];

        while ($parts && in_array($parts[0], $honorifics, true)) {
            array_shift($parts);
        }

        return implode(' ', $parts);
    }

    public static function matches(CaseRecord $case, array $record): bool
    {
        $cnic = self::normalizeCnic($case->cnic);
        $caseName = self::normalizeName($case->name);
        $cmsName = self::normalizeName($record['clientName'] ?? $record['name'] ?? null);

        return strlen($cnic) === 13
            && hash_equals($cnic, self::normalizeCnic($record['cnic'] ?? null))
            && $caseName !== ''
            && $cmsName !== ''
            && hash_equals($caseName, $cmsName);
    }
}
