<?php

declare(strict_types=1);

namespace App\Tests\Property\Domain\Task;

use App\Domain\Task\DoneTask;
use App\Domain\Task\InProgressTask;
use App\Domain\Task\TaskDescription;
use App\Domain\Task\TaskStatus;
use App\Domain\Task\TaskTitle;
use App\Domain\Task\TodoTask;
use App\Tests\Support\PropertyTestCase;

final class TaskPropertyTest extends PropertyTestCase
{
    /**
     * @throws \Throwable
     */
    public function testTaskAlwaysCreatedAsTodoTask(): void
    {
        $this->forAll(self::suchThat(
            static fn(string $s): bool => \trim($s) !== '' && \mb_strlen(\trim($s)) <= 255,
            self::string(),
        ))->then(static function (string $titleStr): void {
            $title = TaskTitle::create($titleStr)->unwrap();
            $description = TaskDescription::empty();

            $result = TodoTask::create($title, $description);

            self::assertInstanceOf(TodoTask::class, $result->unwrap());
            self::assertSame(TaskStatus::Todo, $result->unwrap()->status);
        });
    }

    /**
     * @throws \Throwable
     */
    public function testTaskSerializationRoundTrip(): void
    {
        $this->forAll(
            self::suchThat(
                static fn(string $s): bool => \trim($s) !== '' && \mb_strlen(\trim($s)) <= 255,
                self::string(),
            ),
            self::suchThat(static fn(string $s): bool => \mb_strlen(\trim($s)) <= 1000, self::string()),
        )->then(static function (string $titleStr, string $descStr): void {
            $title = TaskTitle::create($titleStr)->unwrap();
            $description = TaskDescription::create($descStr)->unwrap();

            $task = TodoTask::create($title, $description)->unwrap();
            /** @var array{title: string, description: string, status: string, due_date: string|null} $array */
            $array = $task->toArray();

            self::assertSame(\trim($titleStr), $array['title']);
            self::assertSame(\trim($descStr), $array['description']);
            self::assertSame('todo', $array['status']);
            self::assertNull($array['due_date']);
        });
    }

    /**
     * @throws \Throwable
     */
    public function testStatusTransitionChainTodoToInProgressToDone(): void
    {
        $this->forAll(self::map(
            static fn(int $_): TodoTask => TodoTask::create(
                TaskTitle::create('Task')->unwrap(),
                TaskDescription::empty(),
            )->unwrap(),
            self::choose(0, 50),
        ))->then(static function (TodoTask $task): void {
            self::assertSame(TaskStatus::Todo, $task->status);

            $inProgress = $task->start();
            self::assertInstanceOf(InProgressTask::class, $inProgress->unwrap());
            self::assertSame(TaskStatus::InProgress, $inProgress->unwrap()->status);

            $done = $inProgress->unwrap()->complete();
            self::assertInstanceOf(DoneTask::class, $done->unwrap());
            self::assertSame(TaskStatus::Done, $done->unwrap()->status);
        });
    }

    /**
     * @throws \Throwable
     */
    public function testImmutabilityPreservedAcrossMutations(): void
    {
        $this->forAll(
            self::suchThat(
                static fn(string $s): bool => \trim($s) !== '' && \mb_strlen(\trim($s)) <= 255,
                self::string(),
            ),
            self::suchThat(
                static fn(string $s): bool => \trim($s) !== '' && \mb_strlen(\trim($s)) <= 255,
                self::string(),
            ),
        )->then(static function (string $original, string $updated): void {
            $originalTitle = TaskTitle::create($original)->unwrap();
            $task = TodoTask::create($originalTitle, TaskDescription::empty())->unwrap();

            $updatedTitle = TaskTitle::create($updated)->unwrap();
            $updatedTask = $task->changeTitle($updatedTitle)->unwrap();

            self::assertSame(\trim($original), $task->title->value());
            self::assertSame(\trim($updated), $updatedTask->title->value());
            self::assertTrue($task->id->equals($updatedTask->id));
        });
    }

    /**
     * @throws \Throwable
     */
    public function testConcreteTypePreservedAfterPropertyChanges(): void
    {
        $this->forAll(self::suchThat(
            static fn(string $s): bool => \trim($s) !== '' && \mb_strlen(\trim($s)) <= 255,
            self::string(),
        ))->then(static function (string $newTitleStr): void {
            $newTitle = TaskTitle::create($newTitleStr)->unwrap();

            $todoTask = TodoTask::create(TaskTitle::create('task')->unwrap(), TaskDescription::empty())->unwrap();
            self::assertInstanceOf(TodoTask::class, $todoTask->changeTitle($newTitle)->unwrap());

            $inProgressTask = $todoTask->start()->unwrap();
            self::assertInstanceOf(InProgressTask::class, $inProgressTask->changeTitle($newTitle)->unwrap());

            $doneTask = $inProgressTask->complete()->unwrap();
            self::assertInstanceOf(DoneTask::class, $doneTask->changeTitle($newTitle)->unwrap());
        });
    }
}
