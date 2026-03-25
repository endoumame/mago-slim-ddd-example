<?php

declare(strict_types=1);

namespace App\Application\Task\Delete;

final readonly class DeleteTask
{
    public function __construct(
        public string $id,
    ) {}
}
