<?php

declare(strict_types=1);

namespace App\Domain\Task;

use DateTimeImmutable;
use EndouMame\PhpMonad\Result;
use Override;

use function EndouMame\PhpMonad\Result\ok;

/**
 * A Task in InProgress status. The only valid transition is complete() → DoneTask.
 *
 * @psalm-immutable
 */
final readonly class InProgressTask extends Task
{
    /** @internal Use Task::reconstitute() or TodoTask::start() */
    protected function __construct(
        TaskId $id,
        TaskTitle $title,
        TaskDescription $description,
        TaskPriority $priority,
        ?DueDate $dueDate,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ) {
        parent::__construct(
            $id,
            $title,
            $description,
            TaskStatus::InProgress,
            $priority,
            $dueDate,
            $createdAt,
            $updatedAt,
        );
    }

    /**
     * Complete this task. Transitions to Done.
     *
     * @return Result<DoneTask, never>
     */
    public function complete(): Result
    {
        /** @var Result<DoneTask, never> */
        return ok(Task::reconstitute(
            $this->id,
            $this->title,
            $this->description,
            TaskStatus::Done,
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
