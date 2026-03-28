<?php

declare(strict_types=1);

namespace App\Domain\Task;

use DateTimeImmutable;
use EndouMame\PhpMonad\Result;

use function EndouMame\PhpMonad\Result\ok;

/**
 * Abstract Task aggregate root. Always valid — can only be constructed through
 * factory methods on concrete subclasses (TodoTask, InProgressTask, DoneTask).
 *
 * Each status has its own type, making invalid state transitions impossible at the type level.
 * - TodoTask can only start() → InProgressTask
 * - InProgressTask can only complete() → DoneTask
 * - DoneTask is terminal — no transitions available
 *
 * Immutable: all mutation methods return a new Task instance.
 *
 * @psalm-immutable
 *
 * @api
 */
abstract readonly class Task
{
    protected function __construct(
        public TaskId $id,
        public TaskTitle $title,
        public TaskDescription $description,
        public TaskStatus $status,
        public TaskPriority $priority,
        public ?DueDate $dueDate,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
    ) {}

    /**
     * Reconstitute a Task from persistence. Dispatches to the correct concrete type
     * based on status.
     */
    public static function reconstitute(
        TaskId $id,
        TaskTitle $title,
        TaskDescription $description,
        TaskStatus $status,
        TaskPriority $priority,
        ?DueDate $dueDate,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return match ($status) {
            TaskStatus::Todo => new TodoTask($id, $title, $description, $priority, $dueDate, $createdAt, $updatedAt),
            TaskStatus::InProgress => new InProgressTask(
                $id,
                $title,
                $description,
                $priority,
                $dueDate,
                $createdAt,
                $updatedAt,
            ),
            TaskStatus::Done => new DoneTask($id, $title, $description, $priority, $dueDate, $createdAt, $updatedAt),
        };
    }

    /**
     * Rebuild this task with updated properties, preserving the concrete type.
     *
     * @return static
     */
    abstract protected function rebuild(
        TaskTitle $title,
        TaskDescription $description,
        TaskPriority $priority,
        ?DueDate $dueDate,
        DateTimeImmutable $updatedAt,
    ): static;

    /**
     * @return Result<static, never>
     */
    public function changeTitle(TaskTitle $newTitle): Result
    {
        return ok($this->rebuild(
            $newTitle,
            $this->description,
            $this->priority,
            $this->dueDate,
            new DateTimeImmutable(),
        ));
    }

    /**
     * @return Result<static, never>
     */
    public function changeDescription(TaskDescription $newDescription): Result
    {
        return ok($this->rebuild(
            $this->title,
            $newDescription,
            $this->priority,
            $this->dueDate,
            new DateTimeImmutable(),
        ));
    }

    /**
     * @return Result<static, never>
     */
    public function changeDueDate(?DueDate $newDueDate): Result
    {
        return ok($this->rebuild(
            $this->title,
            $this->description,
            $this->priority,
            $newDueDate,
            new DateTimeImmutable(),
        ));
    }

    /**
     * @return Result<static, never>
     */
    public function changePriority(TaskPriority $newPriority): Result
    {
        return ok($this->rebuild(
            $this->title,
            $this->description,
            $newPriority,
            $this->dueDate,
            new DateTimeImmutable(),
        ));
    }

    /**
     * Serialize to an associative array for API responses.
     *
     * @return array{id: string, title: string, description: string, status: string, priority: string, due_date: string|null, created_at: string, updated_at: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id->value(),
            'title' => $this->title->value(),
            'description' => $this->description->value(),
            'status' => $this->status->value,
            'priority' => $this->priority->value,
            'due_date' => $this->dueDate?->format(),
            'created_at' => $this->createdAt->format(DateTimeImmutable::ATOM),
            'updated_at' => $this->updatedAt->format(DateTimeImmutable::ATOM),
        ];
    }
}
