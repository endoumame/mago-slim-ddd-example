<?php

declare(strict_types=1);

namespace App\Domain\Task\Exception;

use App\Domain\Task\TaskStatus;

final class InvalidTaskStatusTransitionException extends DomainError
{
    public static function notAllowed(TaskStatus $from, TaskStatus $to): self
    {
        return new self(
            "Cannot transition from '{$from->value}' to '{$to->value}'. Only Todo→InProgress→Done is allowed.",
        );
    }
}
