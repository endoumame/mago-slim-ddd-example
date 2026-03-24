<?php

declare(strict_types=1);

namespace App\Application\Task\Delete;

final readonly class DeleteTaskCommand
{
    public function __construct(
        public string $id,
    ) {}
}
