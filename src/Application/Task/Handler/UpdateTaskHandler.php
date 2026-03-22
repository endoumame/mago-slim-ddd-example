<?php

declare(strict_types=1);

namespace App\Application\Task\Handler;

use App\Application\Task\Command\UpdateTaskCommand;
use App\Domain\Task\DueDate;
use App\Domain\Task\Task;
use App\Domain\Task\TaskDescription;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskRepositoryInterface;
use App\Domain\Task\TaskTitle;
use EndouMame\PhpMonad\Result;

use function App\Shared\Option\apply_if_some;
use function App\Shared\Result\bind;
use function EndouMame\PhpMonad\Option\fromValue;
use function EndouMame\PhpMonad\Result\ok;

final readonly class UpdateTaskHandler
{
    public function __construct(
        private TaskRepositoryInterface $repository,
    ) {}

    /**
     * @return Result<Task, \Throwable>
     */
    public function handle(UpdateTaskCommand $command): Result
    {
        $idResult = TaskId::create($command->id);

        return $idResult
            |> bind(fn(TaskId $id): Result => $this->repository->findById($id))
            |> bind(fn(Task $task): Result => $this->applyChanges($task, $command));
    }

    /**
     * @return Result<Task, \Throwable>
     */
    private function applyChanges(Task $task, UpdateTaskCommand $command): Result
    {
        /** @var Result<Task, \Throwable> $result */
        $result = ok($task)
            |> apply_if_some(
                fromValue($command->title),
                static fn(string $title): \Closure => static fn(Task $t): Result => TaskTitle::create($title)
                    |> bind($t->changeTitle(...)),
            )
            |> apply_if_some(
                fromValue($command->description),
                static fn(string $description): \Closure => static fn(Task $t): Result => TaskDescription::create(
                    $description,
                )
                    |> bind($t->changeDescription(...)),
            )
            |> apply_if_some(
                fromValue($command->dueDate),
                static fn(string $date): \Closure => static fn(Task $t): Result => DueDate::create($date)
                    |> bind($t->changeDueDate(...)),
            );

        return $result |> bind(fn(Task $t): Result => $this->repository->save($t));
    }
}
