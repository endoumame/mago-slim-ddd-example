<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Task\Exception\TaskNotFoundException;
use App\Domain\Task\Task;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskRepositoryInterface;
use EndouMame\PhpMonad\Result;
use Override;

use function EndouMame\PhpMonad\Result\err;
use function EndouMame\PhpMonad\Result\ok;

final class InMemoryTaskRepository implements TaskRepositoryInterface
{
    /** @var array<string, Task> */
    private array $tasks = [];

    /**
     * @return Result<Task, \Throwable>
     */
    #[Override]
    public function findById(TaskId $id): Result
    {
        $key = $id->value();

        if (!\array_key_exists($key, $this->tasks)) {
            /** @var Result<Task, \Throwable> */
            return err(TaskNotFoundException::withId($key));
        }

        return ok($this->tasks[$key]);
    }

    /**
     * @return Result<list<Task>, \Throwable>
     */
    #[Override]
    public function findAll(): Result
    {
        return $this->tasks |> \array_values(...) |> ok(...);
    }

    /**
     * @return Result<Task, \Throwable>
     */
    #[Override]
    public function save(Task $task): Result
    {
        $this->tasks[$task->id->value()] = $task;

        return ok($task);
    }

    /**
     * @return Result<true, \Throwable>
     */
    #[Override]
    public function delete(TaskId $id): Result
    {
        $key = $id->value();

        if (!\array_key_exists($key, $this->tasks)) {
            /** @var Result<true, \Throwable> */
            return err(TaskNotFoundException::withId($key));
        }

        unset($this->tasks[$key]);

        return ok(true);
    }
}
