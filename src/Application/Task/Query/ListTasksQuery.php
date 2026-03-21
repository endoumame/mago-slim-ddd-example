<?php

declare(strict_types=1);

namespace App\Application\Task\Query;

final readonly class ListTasksQuery
{
    public function __construct(
        public ?string $status = null,
    ) {}
}
