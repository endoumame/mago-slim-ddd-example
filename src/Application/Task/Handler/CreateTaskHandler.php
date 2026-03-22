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

use function App\Shared\Option\traverse;
use function App\Shared\Result\flat_map;
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

        $descriptionResult = flat_map($titleResult, static fn(TaskTitle $_): ResultInterface => TaskDescription::create($command->description));

        $dueDateResult = flat_map($descriptionResult, static fn(TaskDescription $_): ResultInterface => traverse(
            from_nullable($command->dueDate),
            DueDate::create(...),
        ));

        return flat_map($dueDateResult, function (?DueDate $dueDate) use (
            $titleResult,
            $descriptionResult,
        ): ResultInterface {
            $title = $titleResult->getResult();
            $description = $descriptionResult->getResult();

            return flat_map(
                TodoTask::create($title, $description, $dueDate),
                fn(Task $task): ResultInterface => $this->repository->save($task),
            );
        });
    }
}
