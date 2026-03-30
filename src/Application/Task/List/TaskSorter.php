<?php

declare(strict_types=1);

namespace App\Application\Task\List;

use App\Domain\Task\Task;
use App\Domain\Task\TaskSortDirection;
use App\Domain\Task\TaskSortField;

final class TaskSorter
{
    /**
     * @param list<Task> $tasks
     * @return list<Task>
     */
    public static function sort(array $tasks, TaskSortField $field, TaskSortDirection $direction): array
    {
        $multiplier = $direction === TaskSortDirection::Asc ? 1 : -1;

        \usort($tasks, static fn(Task $a, Task $b): int => match ($field) {
            TaskSortField::Priority => ($a->priority->weight() - $b->priority->weight()) * $multiplier,
            TaskSortField::DueDate => NullLastDueDateCompare::compare($a->dueDate, $b->dueDate, $multiplier),
            TaskSortField::CreatedAt => ($a->createdAt <=> $b->createdAt) * $multiplier,
        });

        return $tasks;
    }
}
