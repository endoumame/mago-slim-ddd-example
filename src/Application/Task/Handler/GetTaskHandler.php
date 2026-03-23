<?php

declare(strict_types=1);

namespace App\Application\Task\Handler;

use App\Application\Task\Query\GetTaskQuery;
use App\Domain\Task\Task;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskRepositoryInterface;
use EndouMame\PhpMonad\Result;

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
        return TaskId::create($query->id)->andThen(fn(TaskId $id): Result => $this->repository->findById($id));
    }
}
