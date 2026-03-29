<?php

declare(strict_types=1);

namespace App\Domain\Task;

enum TaskSortDirection: string
{
    case Asc = 'asc';
    case Desc = 'desc';
}
