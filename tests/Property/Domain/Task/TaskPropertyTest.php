<?php

declare(strict_types=1);

namespace App\Tests\Property\Domain\Task;

use App\Domain\Task\DoneTask;
use App\Domain\Task\InProgressTask;
use App\Domain\Task\TaskDescription;
use App\Domain\Task\TaskStatus;
use App\Domain\Task\TaskTitle;
use App\Domain\Task\TodoTask;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\choose;
use function Eris\Generator\map;
use function Eris\Generator\string;
use function Eris\Generator\suchThat;

final class TaskPropertyTest extends TestCase
{
    use TestTrait;

    /**
     * @throws \Throwable
     */
    public function testTaskAlwaysCreatedAsTodoTask(): void
    {
        $this->forAll(suchThat(
            static fn(string $s): bool => trim($s) !== '' && mb_strlen(trim($s)) <= 255,
            string(),
        ))->then(static function (string $titleStr): void {
            $title = TaskTitle::create($titleStr)->getResult();
            $description = TaskDescription::empty();

            $result = TodoTask::create($title, $description);

            self::assertTrue($result->isSucceeded());
            self::assertInstanceOf(TodoTask::class, $result->getResult());
            self::assertSame(TaskStatus::Todo, $result->getResult()->status);
        });
    }

    /**
     * @throws \Throwable
     */
    public function testTaskSerializationRoundTrip(): void
    {
        $this->forAll(
            suchThat(static fn(string $s): bool => trim($s) !== '' && mb_strlen(trim($s)) <= 255, string()),
            suchThat(static fn(string $s): bool => mb_strlen(trim($s)) <= 1000, string()),
        )->then(static function (string $titleStr, string $descStr): void {
            $title = TaskTitle::create($titleStr)->getResult();
            $description = TaskDescription::create($descStr)->getResult();

            $task = TodoTask::create($title, $description)->getResult();
            /** @var array{title: string, description: string, status: string, due_date: string|null} $array */
            $array = $task->toArray();

            self::assertSame(trim($titleStr), $array['title']);
            self::assertSame(trim($descStr), $array['description']);
            self::assertSame('todo', $array['status']);
            self::assertNull($array['due_date']);
        });
    }

    /**
     * @throws \Throwable
     */
    public function testStatusTransitionChainTodoToInProgressToDone(): void
    {
        $this->forAll(map(
            static fn(int $_): TodoTask => TodoTask::create(
                TaskTitle::create('Task')->getResult(),
                TaskDescription::empty(),
            )->getResult(),
            choose(0, 50),
        ))->then(static function (TodoTask $task): void {
            self::assertSame(TaskStatus::Todo, $task->status);

            $inProgress = $task->start();
            self::assertTrue($inProgress->isSucceeded());
            self::assertInstanceOf(InProgressTask::class, $inProgress->getResult());
            self::assertSame(TaskStatus::InProgress, $inProgress->getResult()->status);

            $done = $inProgress->getResult()->complete();
            self::assertTrue($done->isSucceeded());
            self::assertInstanceOf(DoneTask::class, $done->getResult());
            self::assertSame(TaskStatus::Done, $done->getResult()->status);
        });
    }

    /**
     * @throws \Throwable
     */
    public function testImmutabilityPreservedAcrossMutations(): void
    {
        $this->forAll(
            suchThat(static fn(string $s): bool => trim($s) !== '' && mb_strlen(trim($s)) <= 255, string()),
            suchThat(static fn(string $s): bool => trim($s) !== '' && mb_strlen(trim($s)) <= 255, string()),
        )->then(static function (string $original, string $updated): void {
            $originalTitle = TaskTitle::create($original)->getResult();
            $task = TodoTask::create($originalTitle, TaskDescription::empty())->getResult();

            $updatedTitle = TaskTitle::create($updated)->getResult();
            $updatedTask = $task->changeTitle($updatedTitle)->getResult();

            self::assertSame(trim($original), $task->title->value());
            self::assertSame(trim($updated), $updatedTask->title->value());
            self::assertTrue($task->id->equals($updatedTask->id));
        });
    }

    /**
     * @throws \Throwable
     */
    public function testConcreteTypePreservedAfterPropertyChanges(): void
    {
        $this->forAll(suchThat(
            static fn(string $s): bool => trim($s) !== '' && mb_strlen(trim($s)) <= 255,
            string(),
        ))->then(static function (string $newTitleStr): void {
            $newTitle = TaskTitle::create($newTitleStr)->getResult();

            $todoTask = TodoTask::create(TaskTitle::create('task')->getResult(), TaskDescription::empty())->getResult();
            self::assertInstanceOf(TodoTask::class, $todoTask->changeTitle($newTitle)->getResult());

            $inProgressTask = $todoTask->start()->getResult();
            self::assertInstanceOf(InProgressTask::class, $inProgressTask->changeTitle($newTitle)->getResult());

            $doneTask = $inProgressTask->complete()->getResult();
            self::assertInstanceOf(DoneTask::class, $doneTask->changeTitle($newTitle)->getResult());
        });
    }
}
