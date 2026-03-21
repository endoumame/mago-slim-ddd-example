<?php

declare(strict_types=1);

namespace App\Domain\Task\Exception;

final class InvalidTaskTitleException extends DomainError
{
    public static function empty(): self
    {
        return new self('Task title must not be empty.');
    }

    public static function tooLong(int $length): self
    {
        return new self("Task title must not exceed 255 characters, got {$length}.");
    }
}
