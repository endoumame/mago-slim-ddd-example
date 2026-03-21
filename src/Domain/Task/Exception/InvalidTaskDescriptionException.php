<?php

declare(strict_types=1);

namespace App\Domain\Task\Exception;

final class InvalidTaskDescriptionException extends DomainError
{
    public static function tooLong(int $length): self
    {
        return new self("Task description must not exceed 1000 characters, got {$length}.");
    }
}
