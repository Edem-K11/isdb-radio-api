<?php

namespace App\Models\Concerns;

/**
 * Helper for tables that hold exactly one configuration row.
 */
trait IsSingleton
{
    /**
     * Return the single configuration record, creating it with defaults on
     * first access so the API and the admin panel always have a row to read.
     */
    public static function current(): static
    {
        return static::query()->firstOrCreate([]);
    }
}
