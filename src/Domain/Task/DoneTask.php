<?php

declare(strict_types=1);

namespace App\Domain\Task;

use DateTimeImmutable;

/**
 * A Task in Done status. Terminal state — no further transitions are possible.
 *
 * The absence of any transition method makes invalid state transitions
 * impossible at the type level.
 *
 * @psalm-immutable
 */
final readonly class DoneTask extends Task
{
    /** @internal Use Task::reconstitute() or InProgressTask::complete() */
    protected function __construct(
        TaskId $id,
        TaskTitle $title,
        TaskDescription $description,
        ?DueDate $dueDate,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ) {
        parent::__construct($id, $title, $description, TaskStatus::Done, $dueDate, $createdAt, $updatedAt);
    }

    protected function rebuild(
        TaskTitle $title,
        TaskDescription $description,
        ?DueDate $dueDate,
        DateTimeImmutable $updatedAt,
    ): static {
        return new self($this->id, $title, $description, $dueDate, $this->createdAt, $updatedAt);
    }
}
