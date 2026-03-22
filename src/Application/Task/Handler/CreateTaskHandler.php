<?php

declare(strict_types=1);

namespace App\Application\Task\Handler;

use App\Application\Task\Command\CreateTaskCommand;
use App\Domain\Task\DueDate;
use App\Domain\Task\Task;
use App\Domain\Task\TaskDescription;
use App\Domain\Task\TaskRepositoryInterface;
use App\Domain\Task\TaskTitle;
use App\Domain\Task\TodoTask;
use EndouMame\PhpMonad\Result;

use function App\Shared\Option\traverse_with;
use function App\Shared\Result\bind;
use function EndouMame\PhpMonad\Option\fromValue;

final readonly class CreateTaskHandler
{
    public function __construct(
        private TaskRepositoryInterface $repository,
    ) {}

    /**
     * @return Result<Task, \Throwable>
     */
    public function handle(CreateTaskCommand $command): Result
    {
        return TaskTitle::create($command->title)
            |> bind(
                static fn(TaskTitle $title): Result => TaskDescription::create($command->description)
                    |> bind(
                        static fn(TaskDescription $description): Result => $command->dueDate
                            |> fromValue(...)
                            |> traverse_with(DueDate::create(...))
                            |> bind(
                                static fn(?DueDate $dueDate): Result => TodoTask::create(
                                    $title,
                                    $description,
                                    $dueDate,
                                ),
                            ),
                    ),
            )
            |> bind(fn(Task $task): Result => $this->repository->save($task));
    }
}
