<?php

declare(strict_types=1);

namespace App\Domain\Task;

/**
 * Task status values. Status transitions are enforced at the type level
 * by the concrete Task subclasses (TodoTask, InProgressTask, DoneTask),
 * not by this enum.
 */
enum TaskStatus: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case Done = 'done';
}
