<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Persistence;

use App\Domain\Task\Exception\TaskNotFoundException;
use App\Domain\Task\Task;
use App\Domain\Task\TaskDescription;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskTitle;
use App\Infrastructure\Persistence\InMemoryTaskRepository;
use PHPUnit\Framework\TestCase;

final class InMemoryTaskRepositoryTest extends TestCase
{
    private InMemoryTaskRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new InMemoryTaskRepository();
    }

    public function testSaveAndFindById(): void
    {
        $task = $this->createTask('Test task');

        $saveResult = $this->repository->save($task);
        self::assertTrue($saveResult->isSucceeded());

        $findResult = $this->repository->findById($task->id);
        self::assertTrue($findResult->isSucceeded());
        self::assertSame($task->id->value(), $findResult->getResult()->id->value());
        self::assertSame('Test task', $findResult->getResult()->title->value());
    }

    public function testFindByIdNotFound(): void
    {
        $id = TaskId::generate();

        $result = $this->repository->findById($id);

        self::assertTrue($result->isFailed());
        self::assertInstanceOf(TaskNotFoundException::class, $result->getThrowable());
    }

    public function testFindAllEmpty(): void
    {
        $result = $this->repository->findAll();

        self::assertTrue($result->isSucceeded());
        self::assertCount(0, $result->getResult());
    }

    public function testFindAllWithTasks(): void
    {
        $this->repository->save($this->createTask('Task 1'));
        $this->repository->save($this->createTask('Task 2'));
        $this->repository->save($this->createTask('Task 3'));

        $result = $this->repository->findAll();

        self::assertTrue($result->isSucceeded());
        self::assertCount(3, $result->getResult());
    }

    public function testSaveUpdatesExistingTask(): void
    {
        $task = $this->createTask('Original');
        $this->repository->save($task);

        $updated = $task->changeTitle(TaskTitle::create('Updated')->getResult())->getResult();
        $this->repository->save($updated);

        $result = $this->repository->findById($task->id);
        self::assertSame('Updated', $result->getResult()->title->value());

        $allResult = $this->repository->findAll();
        self::assertCount(1, $allResult->getResult());
    }

    public function testDelete(): void
    {
        $task = $this->createTask('To delete');
        $this->repository->save($task);

        $deleteResult = $this->repository->delete($task->id);
        self::assertTrue($deleteResult->isSucceeded());

        $findResult = $this->repository->findById($task->id);
        self::assertTrue($findResult->isFailed());
    }

    public function testDeleteNonExistent(): void
    {
        $result = $this->repository->delete(TaskId::generate());

        self::assertTrue($result->isFailed());
        self::assertInstanceOf(TaskNotFoundException::class, $result->getThrowable());
    }

    private function createTask(string $titleStr): Task
    {
        $title = TaskTitle::create($titleStr)->getResult();
        $description = TaskDescription::empty();

        return Task::create($title, $description)->getResult();
    }
}
