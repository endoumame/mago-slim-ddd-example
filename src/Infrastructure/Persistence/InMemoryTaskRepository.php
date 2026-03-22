<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Task\Exception\TaskNotFoundException;
use App\Domain\Task\Task;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskRepositoryInterface;
use Psl\Result\ResultInterface;
use Psl\Vec;

use function App\Shared\Result\fail;
use function App\Shared\Result\succeed;

final class InMemoryTaskRepository implements TaskRepositoryInterface
{
    /** @var array<string, Task> */
    private array $tasks = [];

    /**
     * @return ResultInterface<Task>
     */
    public function findById(TaskId $id): ResultInterface
    {
        $key = $id->value();

        if (!isset($this->tasks[$key])) {
            return fail(TaskNotFoundException::withId($key));
        }

        return succeed($this->tasks[$key]);
    }

    /**
     * @return ResultInterface<list<Task>>
     */
    public function findAll(): ResultInterface
    {
        return $this->tasks |> Vec\values(...) |> succeed(...);
    }

    /**
     * @return ResultInterface<Task>
     */
    public function save(Task $task): ResultInterface
    {
        $this->tasks[$task->id->value()] = $task;

        return succeed($task);
    }

    /**
     * @return ResultInterface<true>
     */
    public function delete(TaskId $id): ResultInterface
    {
        $key = $id->value();

        if (!isset($this->tasks[$key])) {
            return fail(TaskNotFoundException::withId($key));
        }

        unset($this->tasks[$key]);

        return succeed(true);
    }
}
