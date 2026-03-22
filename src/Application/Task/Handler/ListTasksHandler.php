<?php

declare(strict_types=1);

namespace App\Application\Task\Handler;

use App\Application\Task\Query\ListTasksQuery;
use App\Domain\Task\Task;
use App\Domain\Task\TaskRepositoryInterface;
use App\Domain\Task\TaskStatus;
use Psl\Result\ResultInterface;
use Psl\Vec;

use function App\Shared\Option\of;
use function App\Shared\Result\bind;
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
        return $this->repository->findAll()
            |> bind(static function (array $tasks) use ($query): ResultInterface {
                $filtered = of($query->status)
                    ->flatMap(static fn(string $s) => of(TaskStatus::tryFrom($s)))
                    ->match(
                        some: static fn(TaskStatus $status) => Vec\filter(
                            $tasks,
                            static fn(Task $task): bool => $task->status === $status,
                        ),
                        none: static fn() => $tasks,
                    );

                return succeed($filtered);
            });
    }
}
