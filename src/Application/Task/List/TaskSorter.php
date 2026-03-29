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
            TaskSortField::DueDate => self::compareDueDate($a, $b, $multiplier),
            TaskSortField::CreatedAt => ($a->createdAt <=> $b->createdAt) * $multiplier,
        });

        return $tasks;
    }

    /**
     * Compare two tasks by due date. Null due dates always sort last regardless of direction.
     * The multiplier is only applied to the comparison of non-null dates.
     */
    private static function compareDueDate(Task $a, Task $b, int $multiplier): int
    {
        $aDate = $a->dueDate;
        $bDate = $b->dueDate;

        if ($aDate === null && $bDate === null) {
            return 0;
        }
        if ($aDate === null) {
            return 1;
        }
        if ($bDate === null) {
            return -1;
        }

        return ($aDate->value() <=> $bDate->value()) * $multiplier;
    }
}
