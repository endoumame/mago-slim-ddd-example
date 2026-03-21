<?php

declare(strict_types=1);

namespace App\Application\Task\Query;

final readonly class GetTaskQuery
{
    public function __construct(
        public string $id,
    ) {}
}
