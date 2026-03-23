<?php

declare(strict_types=1);

namespace App\Application\Task\Handler;

use App\Application\Task\Command\DeleteTaskCommand;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskRepositoryInterface;
use EndouMame\PhpMonad\Result;

final readonly class DeleteTaskHandler
{
    public function __construct(
        private TaskRepositoryInterface $repository,
    ) {}

    /**
     * @return Result<true, \Throwable>
     */
    public function handle(DeleteTaskCommand $command): Result
    {
        return TaskId::create($command->id)->andThen(fn(TaskId $id): Result => $this->repository
            ->findById($id)
            ->andThen(fn(): Result => $this->repository->delete($id)));
    }
}
