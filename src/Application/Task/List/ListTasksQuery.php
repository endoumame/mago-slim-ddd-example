<?php

declare(strict_types=1);

namespace App\Application\Task\List;

final readonly class ListTasksQuery
{
    public function __construct(
        public ?string $status = null,
        public ?string $priority = null,
    ) {}
}
