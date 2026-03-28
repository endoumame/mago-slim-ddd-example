<?php

declare(strict_types=1);

namespace App\Application\Task\Create;

final readonly class CreateTaskCommand
{
    public function __construct(
        public string $title,
        public string $description = '',
        public ?string $dueDate = null,
        public ?string $priority = null,
    ) {}
}
