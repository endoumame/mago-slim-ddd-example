<?php

declare(strict_types=1);

namespace App\Domain\Task;

use App\Domain\Task\Exception\InvalidTaskStatusTransitionException;
use Psl\Result\ResultInterface;

use function App\Shared\Result\fail;
use function App\Shared\Result\succeed;

/**
 * Task status with strict linear transitions: Todo → InProgress → Done.
 */
enum TaskStatus: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case Done = 'done';

    /**
     * Validate a status transition. Only forward transitions are allowed.
     *
     * @return ResultInterface<self>
     */
    public function transitionTo(self $next): ResultInterface
    {
        $allowed = match ($this) {
            self::Todo => $next === self::InProgress,
            self::InProgress => $next === self::Done,
            self::Done => false,
        };

        if (!$allowed) {
            /** @var ResultInterface<self> */
            return fail(InvalidTaskStatusTransitionException::notAllowed($this, $next));
        }

        return succeed($next);
    }
}
