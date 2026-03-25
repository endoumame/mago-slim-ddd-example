<?php

declare(strict_types=1);

namespace App\Application\Task\Delete;

use App\Domain\Task\TaskId;
use App\Domain\Task\TaskRepositoryInterface;
use EndouMame\PhpMonad\Result;

use function EndouMame\PhpMonad\Result\andThen;

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
        /** @var Result<true, \Throwable> */
        return TaskId::create($command->id)
            |> andThen(
                fn(TaskId $id): Result => $this->repository->findById($id)
                    |> andThen(fn(): Result => $this->repository->delete($id)),
            );
    }
}
