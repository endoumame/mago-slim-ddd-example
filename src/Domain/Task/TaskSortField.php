<?php

declare(strict_types=1);

namespace App\Domain\Task;

enum TaskSortField: string
{
    case Priority = 'priority';
    case DueDate = 'due_date';
    case CreatedAt = 'created_at';
}
