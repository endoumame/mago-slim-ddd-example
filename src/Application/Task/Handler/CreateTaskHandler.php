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

use function App\Shared\Result\flat_map;
use function App\Shared\Result\succeed;

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

        $descriptionResult = flat_map($titleResult, static fn(TaskTitle $_): ResultInterface => TaskDescription::create($command->description));

        $dueDateResult = flat_map($descriptionResult, static function (TaskDescription $_) use (
            $command,
        ): ResultInterface {
            if ($command->dueDate === null) {
                return succeed(null);
            }
            return DueDate::create($command->dueDate);
        });

        return flat_map($dueDateResult, function (?DueDate $dueDate) use (
            $titleResult,
            $descriptionResult,
        ): ResultInterface {
            /** @var TaskTitle $title */
            $title = $titleResult->getResult();
            /** @var TaskDescription $description */
            $description = $descriptionResult->getResult();

            return flat_map(
                Task::create($title, $description, $dueDate),
                fn(Task $task): ResultInterface => $this->repository->save($task),
            );
        });
    }
}
