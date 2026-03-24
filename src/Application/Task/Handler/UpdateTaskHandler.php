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

use function EndouMame\PhpMonad\Option\fromValue;
use function EndouMame\PhpMonad\Option\traverse;
use function EndouMame\PhpMonad\Result\andThen;
use function EndouMame\PhpMonad\Result\flat_map_all;
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
        $id = TaskId::create($command->id);
        $title = traverse(fromValue($command->title), TaskTitle::create(...));
        $description = traverse(fromValue($command->description), TaskDescription::create(...));
        $dueDate = traverse(fromValue($command->dueDate), DueDate::create(...));

        /** @var Result<Task, \Throwable> */
        return flat_map_all(
            fn(TaskId $taskId, ?TaskTitle $t, ?TaskDescription $d, ?DueDate $dd): Result => $this->repository->findById(
                $taskId,
            )
                |> andThen(fn(Task $task): Result => $this->applyChanges($task, $t, $d, $dd)),
            $id,
            $title,
            $description,
            $dueDate,
        );
    }

    /**
     * @return Result<Task, \Throwable>
     */
    private function applyChanges(
        Task $task,
        ?TaskTitle $newTitle,
        ?TaskDescription $newDescription,
        ?DueDate $newDueDate,
    ): Result {
        /** @var Result<Task, \Throwable> $result */
        $result = ok($task);

        if ($newTitle !== null) {
            $result = $result |> andThen(static fn(Task $t): Result => $t->changeTitle($newTitle));
        }

        if ($newDescription !== null) {
            $result = $result |> andThen(static fn(Task $t): Result => $t->changeDescription($newDescription));
        }

        if ($newDueDate !== null) {
            $result = $result |> andThen(static fn(Task $t): Result => $t->changeDueDate($newDueDate));
        }

        /** @var Result<Task, \Throwable> */
        return $result |> andThen(fn(Task $t): Result => $this->repository->save($t));
    }
}
