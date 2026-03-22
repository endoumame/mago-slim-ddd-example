<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Task;

use App\Domain\Task\Exception\InvalidTaskIdException;
use App\Domain\Task\TaskId;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class TaskIdTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testCreateWithValidUuid(): void
    {
        $uuid = Uuid::uuid4()->toString();
        $result = TaskId::create($uuid);

        self::assertTrue($result->isSucceeded());
        self::assertSame($uuid, $result->getResult()->value());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithInvalidUuid(): void
    {
        $result = TaskId::create('not-a-uuid');

        self::assertTrue($result->isFailed());
        self::assertInstanceOf(InvalidTaskIdException::class, $result->getThrowable());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithEmptyString(): void
    {
        $result = TaskId::create('');

        self::assertTrue($result->isFailed());
        self::assertInstanceOf(InvalidTaskIdException::class, $result->getThrowable());
    }

    /**
     * @throws \Throwable
     */
    public function testGenerate(): void
    {
        $id = TaskId::generate();

        self::assertTrue(Uuid::isValid($id->value()));
    }

    /**
     * @throws \Throwable
     */
    public function testGenerateProducesUniqueIds(): void
    {
        $id1 = TaskId::generate();
        $id2 = TaskId::generate();

        self::assertFalse($id1->equals($id2));
    }

    /**
     * @throws \Throwable
     */
    public function testEquals(): void
    {
        $uuid = Uuid::uuid4()->toString();
        $id1 = TaskId::create($uuid)->getResult();
        $id2 = TaskId::create($uuid)->getResult();

        self::assertTrue($id1->equals($id2));
    }

    /**
     * @throws \Throwable
     */
    public function testToString(): void
    {
        $uuid = Uuid::uuid4()->toString();
        $id = TaskId::create($uuid)->getResult();

        self::assertSame($uuid, (string) $id);
    }
}
