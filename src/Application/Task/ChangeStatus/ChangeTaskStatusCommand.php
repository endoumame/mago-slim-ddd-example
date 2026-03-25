<?php

declare(strict_types=1);

namespace App\Application\Task\ChangeStatus;

final readonly class ChangeTaskStatusCommand
{
    public function __construct(
        public string $id,
        public string $status,
    ) {}
}
