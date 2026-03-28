<?php

declare(strict_types=1);

namespace App\Domain\Task;

use DateTimeImmutable;
use EndouMame\PhpMonad\Result;
use Override;

use function EndouMame\PhpMonad\Result\ok;

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
        TaskPriority $priority,
        ?DueDate $dueDate,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ) {
        parent::__construct($id, $title, $description, TaskStatus::Todo, $priority, $dueDate, $createdAt, $updatedAt);
    }

    /**
     * Create a new Task. Always starts in Todo status.
     *
     * @return Result<self, never>
     */
    public static function create(
        TaskTitle $title,
        TaskDescription $description,
        ?DueDate $dueDate = null,
        TaskPriority $priority = TaskPriority::Medium,
    ): Result {
        $now = new DateTimeImmutable();

        return ok(
            new self(
                id: TaskId::generate(),
                title: $title,
                description: $description,
                priority: $priority,
                dueDate: $dueDate,
                createdAt: $now,
                updatedAt: $now,
            ),
        );
    }

    /**
     * Start working on this task. Transitions to InProgress.
     *
     * @return Result<InProgressTask, never>
     */
    public function start(): Result
    {
        /** @var Result<InProgressTask, never> */
        return ok(Task::reconstitute(
            $this->id,
            $this->title,
            $this->description,
            TaskStatus::InProgress,
            $this->priority,
            $this->dueDate,
            $this->createdAt,
            new DateTimeImmutable(),
        ));
    }

    #[Override]
    protected function rebuild(
        TaskTitle $title,
        TaskDescription $description,
        TaskPriority $priority,
        ?DueDate $dueDate,
        DateTimeImmutable $updatedAt,
    ): static {
        return new self($this->id, $title, $description, $priority, $dueDate, $this->createdAt, $updatedAt);
    }
}
