<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Task;

use App\Domain\Task\Exception\InvalidTaskDescriptionException;
use App\Domain\Task\TaskDescription;
use PHPUnit\Framework\TestCase;

final class TaskDescriptionTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testCreateWithValidDescription(): void
    {
        $result = TaskDescription::create('A detailed task description');

        static::assertSame('A detailed task description', $result->unwrap()->value());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithEmptyStringSucceeds(): void
    {
        $result = TaskDescription::create('');

        static::assertSame('', $result->unwrap()->value());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateTrimsWhitespace(): void
    {
        $result = TaskDescription::create('  description  ');

        static::assertSame('description', $result->unwrap()->value());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithMaxLengthSucceeds(): void
    {
        $desc = \str_repeat(string: 'a', times: 1000);
        $result = TaskDescription::create($desc);

        static::assertNotNull($result->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithTooLongDescriptionFails(): void
    {
        $desc = \str_repeat(string: 'a', times: 1001);
        $result = TaskDescription::create($desc);

        static::assertInstanceOf(InvalidTaskDescriptionException::class, $result->unwrapErr());
        static::assertStringContainsString('1000', $result->unwrapErr()->getMessage());
    }

    /**
     * @throws \Throwable
     */
    public function testEmptyFactory(): void
    {
        $desc = TaskDescription::empty();

        static::assertSame('', $desc->value());
    }

    /**
     * @throws \Throwable
     */
    public function testEquals(): void
    {
        $desc1 = TaskDescription::create('Same')->unwrap();
        $desc2 = TaskDescription::create('Same')->unwrap();

        static::assertTrue($desc1->equals($desc2));
    }
}
