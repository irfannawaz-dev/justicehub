<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceConfig extends Model
{
    protected $fillable = [
        'config', 'updated_by', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
        ];
    }

    /**
     * Get the latest finance config.
     */
    public static function current(): ?self
    {
        return static::latest()->first();
    }

    /**
     * Get a config value with dot-notation support and fallback to justice_hub defaults.
     * Can be called statically or as an instance method.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $config = static::current();
        if ($config) {
            $value = data_get($config->config, $key);
            if ($value !== null) {
                return $value;
            }
        }
        return config("justice_hub.finance.{$key}", $default);
    }
}
