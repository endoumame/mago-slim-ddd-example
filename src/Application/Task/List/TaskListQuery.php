<?php

declare(strict_types=1);

namespace App\Application\Task\List;

final readonly class TaskListQuery
{
    public function __construct(
        public ?string $status = null,
    ) {}
}
