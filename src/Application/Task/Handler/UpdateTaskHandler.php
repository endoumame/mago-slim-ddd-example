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
use function App\Shared\Result\flat_map;
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

        return flat_map(
            flat_map($idResult, fn(TaskId $id): ResultInterface => $this->repository->findById($id)),
            fn(Task $task): ResultInterface => $this->applyChanges($task, $command),
        );
    }

    /**
     * @return ResultInterface<Task>
     */
    private function applyChanges(Task $task, UpdateTaskCommand $command): ResultInterface
    {
        $applyTitle = apply_if_some(
            from_nullable($command->title),
            static fn(string $title): \Closure => static fn(Task $t): ResultInterface => flat_map(
                TaskTitle::create($title),
                $t->changeTitle(...),
            ),
        );

        $applyDescription = apply_if_some(
            from_nullable($command->description),
            static fn(string $description): \Closure => static fn(Task $t): ResultInterface => flat_map(
                TaskDescription::create($description),
                $t->changeDescription(...),
            ),
        );

        $applyDueDate = apply_if_some(
            from_nullable($command->dueDate),
            static fn(string $date): \Closure => static fn(Task $t): ResultInterface => flat_map(
                DueDate::create($date),
                $t->changeDueDate(...),
            ),
        );

        /** @var ResultInterface<Task> $result */
        $result = $applyDueDate($applyDescription($applyTitle(succeed($task))));

        return flat_map($result, fn(Task $t): ResultInterface => $this->repository->save($t));
    }
}
