<?php

declare(strict_types=1);

namespace App\Application\Task\Create;

use App\Domain\Task\DueDate;
use App\Domain\Task\Task;
use App\Domain\Task\TaskDescription;
use App\Domain\Task\TaskPriority;
use App\Domain\Task\TaskRepositoryInterface;
use App\Domain\Task\TaskTitle;
use App\Domain\Task\TodoTask;
use EndouMame\PhpMonad\Result;

use function EndouMame\PhpMonad\Option\fromValue;
use function EndouMame\PhpMonad\Option\traverse;
use function EndouMame\PhpMonad\Result\andThen;
use function EndouMame\PhpMonad\Result\flat_map_all;

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
        $priority = fromValue($command->priority)
            ->mapOrElse(
                static fn(string $p): TaskPriority => TaskPriority::tryFrom($p) ?? TaskPriority::Medium,
                static fn(): TaskPriority => TaskPriority::Medium,
            );

        /** @var Result<Task, \Throwable> */
        return flat_map_all(
            static fn(TaskTitle $t, TaskDescription $d, ?DueDate $dd): Result => TodoTask::create(
                $t,
                $d,
                $dd,
                $priority,
            ),
            $title,
            $description,
            $dueDate,
        )
            |> andThen(fn(Task $task): Result => $this->repository->save($task));
    }
}
