<?php

declare(strict_types=1);

namespace App\Application\Task\List;

use App\Domain\Task\Task;
use App\Domain\Task\TaskPriority;
use App\Domain\Task\TaskRepositoryInterface;
use App\Domain\Task\TaskStatus;
use EndouMame\PhpMonad\Option;
use EndouMame\PhpMonad\Result;

use function EndouMame\PhpMonad\Option\fromValue;
use function EndouMame\PhpMonad\Result\andThen;
use function EndouMame\PhpMonad\Result\ok;

final readonly class ListTasksHandler
{
    public function __construct(
        private TaskRepositoryInterface $repository,
    ) {}

    /**
     * @return Result<list<Task>, \Throwable>
     */
    public function handle(ListTasksQuery $query): Result
    {
        /** @var Result<list<Task>, \Throwable> */
        return $this->repository->findAll()
            |> andThen(
                /**
                 * @param list<Task> $tasks
                 * @return Result<list<Task>, \Throwable>
                 */
                static function (array $tasks) use ($query): Result {
                    $filtered = fromValue($query->status)
                        ->andThen(static fn(string $s): Option => TaskStatus::tryFrom($s) |> fromValue(...))
                        ->mapOrElse(
                            /** @return list<Task> */
                            static fn(TaskStatus $status): array => \array_values(\array_filter(
                                $tasks,
                                static fn(Task $task): bool => $task->status === $status,
                            )),
                            /** @return list<Task> */
                            static fn(): array => $tasks,
                        );

                    $filtered = fromValue($query->priority)
                        ->andThen(static fn(string $p): Option => TaskPriority::tryFrom($p) |> fromValue(...))
                        ->mapOrElse(
                            /** @return list<Task> */
                            static fn(TaskPriority $priority): array => \array_values(\array_filter(
                                $filtered,
                                static fn(Task $task): bool => $task->priority === $priority,
                            )),
                            /** @return list<Task> */
                            static fn(): array => $filtered,
                        );

                    return ok($filtered);
                },
            );
    }
}
