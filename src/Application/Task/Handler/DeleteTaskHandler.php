<?php

declare(strict_types=1);

namespace App\Application\Task\Handler;

use App\Application\Task\Command\DeleteTaskCommand;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskRepositoryInterface;
use EndouMame\PhpMonad\Result;

use function App\Shared\Result\bind;

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
        $idResult = TaskId::create($command->id);

        return $idResult
            |> bind(
                fn(TaskId $id): Result => $this->repository->findById($id)
                    |> bind(fn(): Result => $this->repository->delete($id)),
            );
    }
}
