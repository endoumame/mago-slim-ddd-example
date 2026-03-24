<?php

declare(strict_types=1);

namespace App\Application\Task\Create;

final readonly class TaskCreateCommand
{
    public function __construct(
        public string $title,
        public string $description = '',
        public ?string $dueDate = null,
    ) {}
}
