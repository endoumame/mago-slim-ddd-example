<?php

declare(strict_types=1);

namespace App\Application\Task\Handler;

use App\Application\Task\Command\DeleteTaskCommand;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskRepositoryInterface;
use Psl\Result\ResultInterface;

use function App\Shared\Result\bind;

final readonly class DeleteTaskHandler
{
    public function __construct(
        private TaskRepositoryInterface $repository,
    ) {}

    /**
     * @return ResultInterface<true>
     */
    public function handle(DeleteTaskCommand $command): ResultInterface
    {
        $idResult = TaskId::create($command->id);

        return $idResult
            |> bind(
                fn(TaskId $id): ResultInterface => $this->repository->findById($id)
                    |> bind(fn(): ResultInterface => $this->repository->delete($id)),
            );
    }
}
