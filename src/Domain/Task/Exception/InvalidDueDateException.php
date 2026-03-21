<?php

declare(strict_types=1);

namespace App\Domain\Task\Exception;

final class InvalidDueDateException extends DomainError
{
    public static function invalidFormat(string $value): self
    {
        return new self("Invalid date format: '{$value}'. Expected Y-m-d.");
    }

    public static function inThePast(string $date): self
    {
        return new self("Due date '{$date}' must not be in the past.");
    }
}
