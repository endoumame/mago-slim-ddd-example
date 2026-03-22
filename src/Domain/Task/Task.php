<?php

declare(strict_types=1);

namespace App\Domain\Task;

use DateTimeImmutable;
use Psl\Result\ResultInterface;

use function App\Shared\Result\bind;
use function App\Shared\Result\succeed;

/**
 * Task aggregate root. Always valid — can only be constructed through
 * factory methods that return ResultInterface.
 *
 * Immutable: all mutation methods return a new Task instance.
 *
 * @psalm-immutable
 */
final readonly class Task
{
    private function __construct(
        public TaskId $id,
        public TaskTitle $title,
        public TaskDescription $description,
        public TaskStatus $status,
        public ?DueDate $dueDate,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
    ) {}

    /**
     * Create a new Task. Status defaults to Todo.
     *
     * @return ResultInterface<Task>
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
                status: TaskStatus::Todo,
                dueDate: $dueDate,
                createdAt: $now,
                updatedAt: $now,
            ),
        );
    }

    /**
     * Reconstitute a Task from persistence. No validation — data is already trusted.
     *
     */
    public static function reconstitute(
        TaskId $id,
        TaskTitle $title,
        TaskDescription $description,
        TaskStatus $status,
        ?DueDate $dueDate,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self($id, $title, $description, $status, $dueDate, $createdAt, $updatedAt);
    }

    /**
     * @return ResultInterface<Task>
     */
    public function changeTitle(TaskTitle $newTitle): ResultInterface
    {
        return succeed(
            new self(
                $this->id,
                $newTitle,
                $this->description,
                $this->status,
                $this->dueDate,
                $this->createdAt,
                new DateTimeImmutable(),
            ),
        );
    }

    /**
     * @return ResultInterface<Task>
     */
    public function changeDescription(TaskDescription $newDescription): ResultInterface
    {
        return succeed(
            new self(
                $this->id,
                $this->title,
                $newDescription,
                $this->status,
                $this->dueDate,
                $this->createdAt,
                new DateTimeImmutable(),
            ),
        );
    }

    /**
     * Transition status. Only forward transitions allowed: Todo -> InProgress -> Done.
     *
     * @return ResultInterface<Task>
     *
     * @throws \Throwable
     */
    public function changeStatus(TaskStatus $newStatus): ResultInterface
    {
        return $this->status->transitionTo($newStatus)
            |> bind(fn(TaskStatus $validatedStatus): ResultInterface => succeed(
                new self(
                    $this->id,
                    $this->title,
                    $this->description,
                    $validatedStatus,
                    $this->dueDate,
                    $this->createdAt,
                    new DateTimeImmutable(),
                ),
            ));
    }

    /**
     * @return ResultInterface<Task>
     */
    public function changeDueDate(?DueDate $newDueDate): ResultInterface
    {
        return succeed(
            new self(
                $this->id,
                $this->title,
                $this->description,
                $this->status,
                $newDueDate,
                $this->createdAt,
                new DateTimeImmutable(),
            ),
        );
    }

    /**
     * Serialize to an associative array for API responses.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id->value(),
            'title' => $this->title->value(),
            'description' => $this->description->value(),
            'status' => $this->status->value,
            'due_date' => $this->dueDate?->format(),
            'created_at' => $this->createdAt->format(DateTimeImmutable::ATOM),
            'updated_at' => $this->updatedAt->format(DateTimeImmutable::ATOM),
        ];
    }
}
