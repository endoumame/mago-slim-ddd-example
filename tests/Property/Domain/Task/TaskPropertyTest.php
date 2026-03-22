<?php

declare(strict_types=1);

namespace App\Tests\Property\Domain\Task;

use App\Domain\Task\Task;
use App\Domain\Task\TaskDescription;
use App\Domain\Task\TaskStatus;
use App\Domain\Task\TaskTitle;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

use function Eris\Generator\choose;
use function Eris\Generator\elements;
use function Eris\Generator\map;
use function Eris\Generator\string;
use function Eris\Generator\suchThat;

final class TaskPropertyTest extends TestCase
{
    use TestTrait;

    /**
     * @throws \Throwable
     */
    public function testTaskAlwaysCreatedWithTodoStatus(): void
    {
        $this->forAll(suchThat(
            static fn(string $s): bool => trim($s) !== '' && mb_strlen(trim($s)) <= 255,
            string(),
        ))->then(static function (string $titleStr): void {
            $title = TaskTitle::create($titleStr)->getResult();
            $description = TaskDescription::empty();

            $result = Task::create($title, $description);

            self::assertTrue($result->isSucceeded());
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

            $task = Task::create($title, $description)->getResult();
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
            static fn(int $_): Task => Task::create(
                TaskTitle::create('Task')->getResult(),
                TaskDescription::empty(),
            )->getResult(),
            choose(0, 50),
        ))->then(static function (Task $task): void {
            self::assertSame(TaskStatus::Todo, $task->status);

            $inProgress = $task->changeStatus(TaskStatus::InProgress);
            self::assertTrue($inProgress->isSucceeded());
            self::assertSame(TaskStatus::InProgress, $inProgress->getResult()->status);

            $done = $inProgress->getResult()->changeStatus(TaskStatus::Done);
            self::assertTrue($done->isSucceeded());
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
            $task = Task::create($originalTitle, TaskDescription::empty())->getResult();

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
    public function testNoReverseTransitionsAllowed(): void
    {
        $this->forAll(elements(
            [TaskStatus::Todo, TaskStatus::Done],
            [TaskStatus::InProgress, TaskStatus::Todo],
            [TaskStatus::Done, TaskStatus::Todo],
            [TaskStatus::Done, TaskStatus::InProgress],
        ))->then(static function (array $pair): void {
            /** @var TaskStatus $from */
            $from = $pair[0];
            /** @var TaskStatus $to */
            $to = $pair[1];
            $result = $from->transitionTo($to);
            self::assertTrue($result->isFailed(), "Expected failure for {$from->value} -> {$to->value}");
        });
    }
}
