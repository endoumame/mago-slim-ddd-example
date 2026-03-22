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

use function App\Shared\Option\traverse;
use function Psl\Option\from_nullable;
use function App\Shared\Result\bind;

final readonly class CreateTaskHandler
{
    public function __construct(
        private TaskRepositoryInterface $repository,
    ) {}

    /**
     * @return ResultInterface<Task>
     */
    public function handle(CreateTaskCommand $command): ResultInterface
    {
        $titleResult = TaskTitle::create($command->title);

        $descriptionResult = $titleResult
            |> bind(static fn(TaskTitle $_): ResultInterface => TaskDescription::create($command->description));

        $dueDateResult = $descriptionResult
            |> bind(static fn(TaskDescription $_): ResultInterface =>
                traverse(from_nullable($command->dueDate), DueDate::create(...)));

        return $dueDateResult
            |> bind(function (?DueDate $dueDate) use ($titleResult, $descriptionResult): ResultInterface {
                /** @var TaskTitle $title */
                $title = $titleResult->getResult();
                /** @var TaskDescription $description */
                $description = $descriptionResult->getResult();

                return Task::create($title, $description, $dueDate)
                    |> bind(fn(Task $task): ResultInterface => $this->repository->save($task));
            });
    }
}
