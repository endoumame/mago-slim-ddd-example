<?php

declare(strict_types=1);

namespace App\Application\Task\Get;

final readonly class TaskGetQuery
{
    public function __construct(
        public string $id,
    ) {}
}
