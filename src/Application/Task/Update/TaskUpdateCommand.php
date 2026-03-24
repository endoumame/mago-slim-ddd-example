<?php

declare(strict_types=1);

namespace App\Application\Task\Update;

final readonly class TaskUpdateCommand
{
    public function __construct(
        public string $id,
        public ?string $title = null,
        public ?string $description = null,
        public ?string $dueDate = null,
    ) {}
}
