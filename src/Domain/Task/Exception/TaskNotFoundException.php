<?php

declare(strict_types=1);

namespace App\Domain\Task\Exception;

final class TaskNotFoundException extends DomainError
{
    public static function withId(string $id): self
    {
        return new self("Task with ID '{$id}' was not found.");
    }
}
