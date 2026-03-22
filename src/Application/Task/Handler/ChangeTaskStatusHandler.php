<?php

declare(strict_types=1);

namespace App\Application\Task\Handler;

use App\Application\Task\Command\ChangeTaskStatusCommand;
use App\Domain\Task\Exception\InvalidTaskStatusTransitionException;
use App\Domain\Task\InProgressTask;
use App\Domain\Task\Task;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskRepositoryInterface;
use App\Domain\Task\TaskStatus;
use App\Domain\Task\TodoTask;
use Psl\Result\ResultInterface;

use function App\Shared\Option\ok_or_err;
use function App\Shared\Result\bind;
use function App\Shared\Result\fail;
use function Psl\Option\from_nullable;

final readonly class ChangeTaskStatusHandler
{
    public function __construct(
        private TaskRepositoryInterface $repository,
    ) {}

    /**
     * @return ResultInterface<Task>
     */
    public function handle(ChangeTaskStatusCommand $command): ResultInterface
    {
        /** @var ResultInterface<TaskStatus> $statusResult */
        $statusResult = TaskStatus::tryFrom($command->status)
            |> from_nullable(...)
            |> ok_or_err(
                new \InvalidArgumentException(
                    "Invalid status: '{$command->status}'. Must be one of: todo, in_progress, done.",
                ),
            );

        $idResult = $statusResult |> bind(static fn(TaskStatus $_): ResultInterface => TaskId::create($command->id));

        return $idResult
            |> bind(fn(TaskId $id): ResultInterface => $this->repository->findById($id))
            |> bind(fn(Task $task): ResultInterface => $this->transitionTo($task, $statusResult->getResult()))
            |> bind(fn(Task $updated): ResultInterface => $this->repository->save($updated));
    }

    /**
     * Dispatch status transition based on the concrete task type.
     * Invalid transitions are prevented by the type system — only valid
     * transition methods exist on each concrete type.
     *
     * @return ResultInterface<Task>
     */
    private function transitionTo(Task $task, TaskStatus $targetStatus): ResultInterface
    {
        return match (true) {
            $task instanceof TodoTask && $targetStatus === TaskStatus::InProgress => $task->start(),
            $task instanceof InProgressTask && $targetStatus === TaskStatus::Done => $task->complete(),
            default => fail(InvalidTaskStatusTransitionException::notAllowed($task->status, $targetStatus)),
        };
    }
}
