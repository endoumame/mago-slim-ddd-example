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

use function App\Shared\Option\traverse;
use function App\Shared\Result\flat_map_all;
use function EndouMame\PhpMonad\Option\fromValue;
use function EndouMame\PhpMonad\Result\andThen;

final readonly class CreateTaskHandler
{
    public function __construct(
        private TaskRepositoryInterface $repository,
    ) {}

    /**
     * @return Result<Task, \Throwable>
     *
     * @throws \Throwable
     */
    public function handle(CreateTaskCommand $command): Result
    {
        $title = TaskTitle::create($command->title);
        $description = TaskDescription::create($command->description);
        $dueDate = traverse(fromValue($command->dueDate), DueDate::create(...));

        /** @var Result<Task, \Throwable> */
        return flat_map_all(TodoTask::create(...), $title, $description, $dueDate)
            |> andThen(fn(Task $task): Result => $this->repository->save($task));
    }
}
