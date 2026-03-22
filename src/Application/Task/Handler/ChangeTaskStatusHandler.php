<?php

declare(strict_types=1);

namespace App\Application\Task\Handler;

use App\Application\Task\Command\ChangeTaskStatusCommand;
use App\Domain\Task\Task;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskRepositoryInterface;
use App\Domain\Task\TaskStatus;
use Psl\Result\ResultInterface;

use function App\Shared\Result\bind;
use function App\Shared\Result\fail;

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
        $status = TaskStatus::tryFrom($command->status);
        if ($status === null) {
            return fail(
                new \InvalidArgumentException(
                    "Invalid status: '{$command->status}'. Must be one of: todo, in_progress, done.",
                ),
            );
        }

        $idResult = TaskId::create($command->id);

        return $idResult
            |> bind(fn(TaskId $id): ResultInterface => $this->repository->findById($id))
            |> bind(static fn(Task $task): ResultInterface => $task->changeStatus($status))
            |> bind(fn(Task $updated): ResultInterface => $this->repository->save($updated));
    }
}
