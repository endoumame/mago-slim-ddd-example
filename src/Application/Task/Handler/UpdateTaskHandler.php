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
        return TaskId::create($command->id)
            ->andThen(fn(TaskId $id): Result => $this->repository->findById($id))
            ->andThen(fn(Task $task): Result => $this->applyChanges($task, $command));
    }

    /**
     * @return Result<Task, \Throwable>
     */
    private function applyChanges(Task $task, UpdateTaskCommand $command): Result
    {
        /** @var Result<Task, \Throwable> $result */
        $result = ok($task);

        if ($command->title !== null) {
            $title = $command->title;
            $result = $result->andThen(static fn(Task $t): Result => TaskTitle::create($title)->andThen(
                $t->changeTitle(...),
            ));
        }

        if ($command->description !== null) {
            $description = $command->description;
            $result = $result->andThen(static fn(Task $t): Result => TaskDescription::create($description)->andThen(
                $t->changeDescription(...),
            ));
        }

        if ($command->dueDate !== null) {
            $dueDate = $command->dueDate;
            $result = $result->andThen(static fn(Task $t): Result => DueDate::create($dueDate)->andThen(
                $t->changeDueDate(...),
            ));
        }

        return $result->andThen(fn(Task $t): Result => $this->repository->save($t));
    }
}
