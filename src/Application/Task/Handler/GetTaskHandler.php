<?php

declare(strict_types=1);

namespace App\Application\Task\Handler;

use App\Application\Task\Query\GetTaskQuery;
use App\Domain\Task\Task;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskRepositoryInterface;
use Psl\Result\ResultInterface;

use function App\Shared\Result\flat_map;

final readonly class GetTaskHandler
{
    public function __construct(
        private TaskRepositoryInterface $repository,
    ) {}

    /**
     * @return ResultInterface<Task>
     */
    public function handle(GetTaskQuery $query): ResultInterface
    {
        return flat_map(TaskId::create($query->id), fn(TaskId $id): ResultInterface => $this->repository->findById(
            $id,
        ));
    }
}
