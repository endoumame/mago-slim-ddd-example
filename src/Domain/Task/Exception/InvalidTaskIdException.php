<?php

declare(strict_types=1);

namespace App\Domain\Task\Exception;

final class InvalidTaskIdException extends DomainError
{
    public static function invalidFormat(string $value): self
    {
        return new self("Invalid task ID format: '{$value}'. Must be a valid UUID v4.");
    }
}
