<?php

namespace App\Traits;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Shorthand trait for Spatie activity logging.
 * Logs all changes on create, update, and delete.
 *
 * Usage: just `use Auditable;` in your model.
 */
trait Auditable
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $model = class_basename($this);
                $identifier = $this->getKey();
                return "{$model} #{$identifier} was {$eventName}";
            });
    }
}
