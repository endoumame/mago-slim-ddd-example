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

use function App\Shared\Result\flat_map;
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

        return flat_map($idResult, fn(TaskId $id): ResultInterface => flat_map(
            $this->repository->findById($id),
            fn(Task $task): ResultInterface => $this->applyChanges($task, $command),
        ));
    }

    /**
     * @return ResultInterface<Task>
     */
    private function applyChanges(Task $task, UpdateTaskCommand $command): ResultInterface
    {
        $result = succeed($task);

        if ($command->title !== null) {
            $result = flat_map($result, static fn(Task $t): ResultInterface => flat_map(
                TaskTitle::create($command->title),
                $t->changeTitle(...),
            ));
        }

        if ($command->description !== null) {
            $result = flat_map($result, static fn(Task $t): ResultInterface => flat_map(
                TaskDescription::create($command->description),
                $t->changeDescription(...),
            ));
        }

        if ($command->dueDate !== null) {
            $result = flat_map($result, static fn(Task $t): ResultInterface => flat_map(
                DueDate::create($command->dueDate),
                $t->changeDueDate(...),
            ));
        }

        return flat_map($result, fn(Task $t): ResultInterface => $this->repository->save($t));
    }
}
