<?php

declare(strict_types=1);

namespace App\Application\Task\Get;

use App\Domain\Task\Task;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskRepositoryInterface;
use EndouMame\PhpMonad\Result;

use function EndouMame\PhpMonad\Result\andThen;

final readonly class GetTaskHandler
{
    public function __construct(
        private TaskRepositoryInterface $repository,
    ) {}

    /**
     * @return Result<Task, \Throwable>
     */
    public function handle(GetTaskQuery $query): Result
    {
        /** @var Result<Task, \Throwable> */
        return TaskId::create($query->id) |> andThen(fn(TaskId $id): Result => $this->repository->findById($id));
    }
}
