<?php

declare(strict_types=1);

namespace App\Application\Task\Handler;

use App\Application\Task\Command\ChangeTaskStatusCommand;
use App\Domain\Task\Task;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskRepositoryInterface;
use App\Domain\Task\TaskStatus;
use Psl\Result\ResultInterface;

use function App\Shared\Result\fail;
use function App\Shared\Result\flat_map;

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

        return flat_map($idResult, fn(TaskId $id): ResultInterface => flat_map(
            $this->repository->findById($id),
            fn(Task $task): ResultInterface => flat_map(
                $task->changeStatus($status),
                fn(Task $updated): ResultInterface => $this->repository->save($updated),
            ),
        ));
    }
}
