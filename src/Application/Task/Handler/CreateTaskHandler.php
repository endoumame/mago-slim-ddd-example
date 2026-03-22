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
use Psl\Result\ResultInterface;

use function App\Shared\Option\traverse_with;
use function App\Shared\Result\bind;
use function Psl\Option\from_nullable;

final readonly class CreateTaskHandler
{
    public function __construct(
        private TaskRepositoryInterface $repository,
    ) {}

    /**
     * @return ResultInterface<Task>
     *
     * @throws \Throwable
     */
    public function handle(CreateTaskCommand $command): ResultInterface
    {
        return TaskTitle::create($command->title)
            |> bind(
                fn(TaskTitle $title): ResultInterface => TaskDescription::create($command->description)
                    |> bind(
                        static fn(TaskDescription $description): ResultInterface => $command->dueDate
                            |> from_nullable(...)
                            |> traverse_with(DueDate::create(...))
                            |> bind(
                                static fn(?DueDate $dueDate): ResultInterface => TodoTask::create(
                                    $title,
                                    $description,
                                    $dueDate,
                                ),
                            ),
                    ),
            )
            |> bind(fn(Task $task): ResultInterface => $this->repository->save($task));
    }
}
