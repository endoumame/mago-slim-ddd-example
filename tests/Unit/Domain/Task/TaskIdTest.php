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

        static::assertSame($uuid, $result->unwrap()->value());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithInvalidUuid(): void
    {
        $result = TaskId::create('not-a-uuid');

        static::assertInstanceOf(InvalidTaskIdException::class, $result->unwrapErr());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithEmptyString(): void
    {
        $result = TaskId::create('');

        static::assertInstanceOf(InvalidTaskIdException::class, $result->unwrapErr());
    }

    /**
     * @throws \Throwable
     */
    public function testGenerate(): void
    {
        $id = TaskId::generate();

        static::assertTrue(Uuid::isValid($id->value()));
    }

    /**
     * @throws \Throwable
     */
    public function testGenerateProducesUniqueIds(): void
    {
        $id1 = TaskId::generate();
        $id2 = TaskId::generate();

        static::assertFalse($id1->equals($id2));
    }

    /**
     * @throws \Throwable
     */
    public function testEquals(): void
    {
        $uuid = Uuid::uuid4()->toString();
        $id1 = TaskId::create($uuid)->unwrap();
        $id2 = TaskId::create($uuid)->unwrap();

        static::assertTrue($id1->equals($id2));
    }

    /**
     * @throws \Throwable
     */
    public function testToString(): void
    {
        $uuid = Uuid::uuid4()->toString();
        $id = TaskId::create($uuid)->unwrap();

        static::assertSame($uuid, (string) $id);
    }
}
