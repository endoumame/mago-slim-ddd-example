<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Persistence;

use App\Domain\Task\Exception\TaskNotFoundException;
use App\Domain\Task\Task;
use App\Domain\Task\TaskDescription;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskTitle;
use App\Domain\Task\TodoTask;
use App\Infrastructure\Persistence\InMemoryTaskRepository;
use PHPUnit\Framework\TestCase;

final class InMemoryTaskRepositoryTest extends TestCase
{
    private InMemoryTaskRepository $repository;

    #[\Override]
    protected function setUp(): void
    {
        $this->repository = new InMemoryTaskRepository();
    }

    /**
     * @throws \Throwable
     */
    public function testSaveAndFindById(): void
    {
        $task = $this->createTask('Test task');

        $saveResult = $this->repository->save($task);
        self::assertNotNull($saveResult->unwrap());

        $findResult = $this->repository->findById($task->id);
        self::assertSame($task->id->value(), $findResult->unwrap()->id->value());
        self::assertSame('Test task', $findResult->unwrap()->title->value());
    }

    /**
     * @throws \Throwable
     */
    public function testFindByIdNotFound(): void
    {
        $id = TaskId::generate();

        $result = $this->repository->findById($id);

        self::assertInstanceOf(TaskNotFoundException::class, $result->unwrapErr());
    }

    /**
     * @throws \Throwable
     */
    public function testFindAllEmpty(): void
    {
        $result = $this->repository->findAll();

        self::assertCount(0, $result->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testFindAllWithTasks(): void
    {
        $this->repository->save($this->createTask('Task 1'));
        $this->repository->save($this->createTask('Task 2'));
        $this->repository->save($this->createTask('Task 3'));

        $result = $this->repository->findAll();

        self::assertCount(3, $result->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testSaveUpdatesExistingTask(): void
    {
        $task = $this->createTask('Original');
        $this->repository->save($task);

        $updated = $task->changeTitle(TaskTitle::create('Updated')->unwrap())->unwrap();
        $this->repository->save($updated);

        $result = $this->repository->findById($task->id);
        self::assertSame('Updated', $result->unwrap()->title->value());

        $allResult = $this->repository->findAll();
        self::assertCount(1, $allResult->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testDelete(): void
    {
        $task = $this->createTask('To delete');
        $this->repository->save($task);

        $deleteResult = $this->repository->delete($task->id);
        self::assertNotNull($deleteResult->unwrap());

        $findResult = $this->repository->findById($task->id);
        self::assertInstanceOf(TaskNotFoundException::class, $findResult->unwrapErr());
    }

    /**
     * @throws \Throwable
     */
    public function testDeleteNonExistent(): void
    {
        $result = $this->repository->delete(TaskId::generate());

        self::assertInstanceOf(TaskNotFoundException::class, $result->unwrapErr());
    }

    /**
     * @throws \Throwable
     */
    private function createTask(string $titleStr): Task
    {
        $title = TaskTitle::create($titleStr)->unwrap();
        $description = TaskDescription::empty();

        return TodoTask::create($title, $description)->unwrap();
    }
}
