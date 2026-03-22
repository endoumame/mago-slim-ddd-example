<?php

declare(strict_types=1);

namespace App\Application\Task\Handler;

use App\Application\Task\Query\ListTasksQuery;
use App\Domain\Task\Task;
use App\Domain\Task\TaskRepositoryInterface;
use App\Domain\Task\TaskStatus;
use Psl\Option\Option;
use Psl\Result\ResultInterface;
use Psl\Vec;

use function App\Shared\Result\bind;
use function App\Shared\Result\succeed;
use function Psl\Option\from_nullable;

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
            |> bind(
                /**
                 * @param list<Task> $tasks
                 * @return ResultInterface<list<Task>>
                 */
                static function (array $tasks) use ($query): ResultInterface {
                    $filtered = from_nullable($query->status)
                        ->andThen(static fn(string $s): Option => from_nullable(TaskStatus::tryFrom($s)))
                        ->proceed(
                            static fn(TaskStatus $status): array => Vec\filter(
                                $tasks,
                                static fn(Task $task): bool => $task->status === $status,
                            ),
                            static fn(): array => $tasks,
                        );

                    return succeed($filtered);
                },
            );
    }
}
