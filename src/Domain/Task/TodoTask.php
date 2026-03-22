<?php

declare(strict_types=1);

namespace App\Domain\Task;

use DateTimeImmutable;
use Override;
use Psl\Result\ResultInterface;

use function App\Shared\Result\succeed;

/**
 * A Task in Todo status. The only valid transition is start() → InProgressTask.
 *
 * @psalm-immutable
 */
final readonly class TodoTask extends Task
{
    /** @internal Use TodoTask::create() or Task::reconstitute() */
    protected function __construct(
        TaskId $id,
        TaskTitle $title,
        TaskDescription $description,
        ?DueDate $dueDate,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ) {
        parent::__construct($id, $title, $description, TaskStatus::Todo, $dueDate, $createdAt, $updatedAt);
    }

    /**
     * Create a new Task. Always starts in Todo status.
     *
     * @return ResultInterface<self>
     */
    public static function create(
        TaskTitle $title,
        TaskDescription $description,
        ?DueDate $dueDate = null,
    ): ResultInterface {
        $now = new DateTimeImmutable();

        return succeed(
            new self(
                id: TaskId::generate(),
                title: $title,
                description: $description,
                dueDate: $dueDate,
                createdAt: $now,
                updatedAt: $now,
            ),
        );
    }

    /**
     * Start working on this task. Transitions to InProgress.
     *
     * @return ResultInterface<InProgressTask>
     */
    public function start(): ResultInterface
    {
        /** @var ResultInterface<InProgressTask> */
        return succeed(Task::reconstitute(
            $this->id,
            $this->title,
            $this->description,
            TaskStatus::InProgress,
            $this->dueDate,
            $this->createdAt,
            new DateTimeImmutable(),
        ));
    }

    #[Override]
    protected function rebuild(
        TaskTitle $title,
        TaskDescription $description,
        ?DueDate $dueDate,
        DateTimeImmutable $updatedAt,
    ): static {
        return new self($this->id, $title, $description, $dueDate, $this->createdAt, $updatedAt);
    }
}
