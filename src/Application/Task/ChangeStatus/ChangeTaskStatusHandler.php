<?php

declare(strict_types=1);

namespace App\Application\Task\ChangeStatus;

use App\Domain\Task\Exception\InvalidTaskStatusTransitionException;
use App\Domain\Task\InProgressTask;
use App\Domain\Task\Task;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskRepositoryInterface;
use App\Domain\Task\TaskStatus;
use App\Domain\Task\TodoTask;
use EndouMame\PhpMonad\Result;

use function EndouMame\PhpMonad\Option\fromValue;
use function EndouMame\PhpMonad\Option\okOr;
use function EndouMame\PhpMonad\Result\andThen;
use function EndouMame\PhpMonad\Result\err;
use function EndouMame\PhpMonad\Result\flat_map_all;

final readonly class ChangeTaskStatusHandler
{
    public function __construct(
        private TaskRepositoryInterface $repository,
    ) {}

    /**
     * @return Result<Task, \Throwable>
     */
    public function handle(ChangeTaskStatus $command): Result
    {
        /** @var Result<TaskStatus, \Throwable> */
        $status = TaskStatus::tryFrom($command->status)
            |> fromValue(...)
            |> okOr(
                new \InvalidArgumentException(
                    "Invalid status: '{$command->status}'. Must be one of: todo, in_progress, done.",
                ),
            );

        $id = TaskId::create($command->id);

        /** @var Result<Task, \Throwable> */
        return flat_map_all(
            fn(TaskStatus $s, TaskId $taskId): Result => $this->repository->findById($taskId)
                |> andThen(fn(Task $task): Result => $this->transitionTo($task, $s))
                |> andThen(fn(Task $updated): Result => $this->repository->save($updated)),
            $status,
            $id,
        );
    }

    /**
     * Dispatch status transition based on the concrete task type.
     * Invalid transitions are prevented by the type system — only valid
     * transition methods exist on each concrete type.
     *
     * @return Result<Task, \Throwable>
     */
    private function transitionTo(Task $task, TaskStatus $targetStatus): Result
    {
        /** @var Result<Task, \Throwable> */
        return match (true) {
            $task instanceof TodoTask && $targetStatus === TaskStatus::InProgress => $task->start(),
            $task instanceof InProgressTask && $targetStatus === TaskStatus::Done => $task->complete(),
            default => err(InvalidTaskStatusTransitionException::notAllowed($task->status, $targetStatus)),
        };
    }
}
