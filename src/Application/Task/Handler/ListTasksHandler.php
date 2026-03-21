<?php

declare(strict_types=1);

namespace App\Application\Task\Handler;

use App\Application\Task\Query\ListTasksQuery;
use App\Domain\Task\Task;
use App\Domain\Task\TaskRepositoryInterface;
use App\Domain\Task\TaskStatus;
use Psl\Result\ResultInterface;
use Psl\Vec;

use function App\Shared\Result\succeed;

final readonly class ListTasksHandler
{
    public function __construct(
        private TaskRepositoryInterface $repository,
    ) {}

    /**
     * @return ResultInterface<list<Task>>
     */
    public function handle(ListTasksQuery $query): ResultInterface
    {
        $result = $this->repository->findAll();

        if ($result->isFailed()) {
            return $result;
        }

        $tasks = $result->getResult();

        if ($query->status !== null) {
            $statusFilter = TaskStatus::tryFrom($query->status);
            if ($statusFilter !== null) {
                $tasks = Vec\filter($tasks, static fn(Task $task): bool => $task->status === $statusFilter);
            }
        }

        return succeed($tasks);
    }
}
