<?php

declare(strict_types=1);

namespace App\Application\Task\Handler;

use App\Application\Task\Command\CreateTaskCommand;
use App\Domain\Task\DueDate;
use App\Domain\Task\Task;
use App\Domain\Task\TaskDescription;
use App\Domain\Task\TaskRepositoryInterface;
use App\Domain\Task\TaskTitle;
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
        $titleResult = TaskTitle::create($command->title);

        $descriptionResult = $titleResult
            |> bind(static fn(TaskTitle $_): ResultInterface => TaskDescription::create($command->description));

        $dueDateResult = $descriptionResult
            |> bind(static fn(TaskDescription $_): ResultInterface => $command->dueDate
                |> from_nullable(...)
                |> traverse_with(DueDate::create(...)),
            );

        return $dueDateResult
            |> bind(function (?DueDate $dueDate) use ($titleResult, $descriptionResult): ResultInterface {
                $title = $titleResult->getResult();
                $description = $descriptionResult->getResult();

                return Task::create($title, $description, $dueDate)
                    |> bind(fn(Task $task): ResultInterface => $this->repository->save($task));
            });
    }
}
