<?php

declare(strict_types=1);

namespace App\Application\Task\List;

use App\Domain\Task\DueDate;

/**
 * Compares nullable DueDates with null-last semantics.
 * Null values always sort last regardless of sort direction.
 */
final class NullLastDueDateCompare
{
    public static function compare(?DueDate $a, ?DueDate $b, int $multiplier): int
    {
        if ($a === null) {
            return $b === null ? 0 : 1;
        }
        if ($b === null) {
            return -1;
        }

        return ($a->value() <=> $b->value()) * $multiplier;
    }
}
