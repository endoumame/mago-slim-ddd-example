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
use Psl\Result\ResultInterface;

use function App\Shared\Option\apply_if_some;
use function App\Shared\Result\bind;
use function App\Shared\Result\succeed;
use function Psl\Option\from_nullable;

final readonly class UpdateTaskHandler
{
    public function __construct(
        private TaskRepositoryInterface $repository,
    ) {}

    /**
     * @return ResultInterface<Task>
     */
    public function handle(UpdateTaskCommand $command): ResultInterface
    {
        $idResult = TaskId::create($command->id);

        return $idResult
            |> bind(fn(TaskId $id): ResultInterface => $this->repository->findById($id))
            |> bind(fn(Task $task): ResultInterface => $this->applyChanges($task, $command));
    }

    /**
     * @return ResultInterface<Task>
     */
    private function applyChanges(Task $task, UpdateTaskCommand $command): ResultInterface
    {
        /** @var ResultInterface<Task> $result */
        $result = succeed($task)
            |> apply_if_some(
                from_nullable($command->title),
                static fn(string $title): \Closure => static fn(Task $t): ResultInterface => TaskTitle::create($title)
                    |> bind($t->changeTitle(...)),
            )
            |> apply_if_some(
                from_nullable($command->description),
                static fn(string $description): \Closure => static fn(Task $t): ResultInterface => TaskDescription::create(
                    $description,
                )
                    |> bind($t->changeDescription(...)),
            )
            |> apply_if_some(
                from_nullable($command->dueDate),
                static fn(string $date): \Closure => static fn(Task $t): ResultInterface => DueDate::create($date)
                    |> bind($t->changeDueDate(...)),
            );

        return $result |> bind(fn(Task $t): ResultInterface => $this->repository->save($t));
    }
}
