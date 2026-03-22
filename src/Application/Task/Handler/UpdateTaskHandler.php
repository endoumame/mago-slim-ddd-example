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

use function App\Shared\Result\bind;
use function App\Shared\Result\succeed;

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
        $result = succeed($task);

        if ($command->title !== null) {
            $result = $result
                |> bind(
                    static fn(Task $t): ResultInterface => TaskTitle::create($command->title)
                        |> bind($t->changeTitle(...)),
                );
        }

        if ($command->description !== null) {
            $result = $result
                |> bind(
                    static fn(Task $t): ResultInterface => TaskDescription::create($command->description)
                        |> bind($t->changeDescription(...)),
                );
        }

        if ($command->dueDate !== null) {
            $result = $result
                |> bind(
                    static fn(Task $t): ResultInterface => DueDate::create($command->dueDate)
                        |> bind($t->changeDueDate(...)),
                );
        }

        return $result |> bind(fn(Task $t): ResultInterface => $this->repository->save($t));
    }
}
