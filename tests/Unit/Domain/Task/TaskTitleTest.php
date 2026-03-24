<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Task;

use App\Domain\Task\Exception\InvalidTaskTitleException;
use App\Domain\Task\TaskTitle;
use PHPUnit\Framework\TestCase;

final class TaskTitleTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testCreateWithValidTitle(): void
    {
        $result = TaskTitle::create('Buy groceries');

        static::assertSame('Buy groceries', $result->unwrap()->value());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateTrimsWhitespace(): void
    {
        $result = TaskTitle::create('  Buy groceries  ');

        static::assertSame('Buy groceries', $result->unwrap()->value());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithEmptyStringFails(): void
    {
        $result = TaskTitle::create('');

        static::assertInstanceOf(InvalidTaskTitleException::class, $result->unwrapErr());
        static::assertStringContainsString('empty', $result->unwrapErr()->getMessage());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithWhitespaceOnlyFails(): void
    {
        $result = TaskTitle::create('   ');

        static::assertInstanceOf(InvalidTaskTitleException::class, $result->unwrapErr());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithMaxLengthSucceeds(): void
    {
        $title = \str_repeat(string: 'a', times: 255);
        $result = TaskTitle::create($title);

        static::assertNotNull($result->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithTooLongTitleFails(): void
    {
        $title = \str_repeat(string: 'a', times: 256);
        $result = TaskTitle::create($title);

        static::assertInstanceOf(InvalidTaskTitleException::class, $result->unwrapErr());
        static::assertStringContainsString('255', $result->unwrapErr()->getMessage());
    }

    /**
     * @throws \Throwable
     */
    public function testEquals(): void
    {
        $title1 = TaskTitle::create('Same title')->unwrap();
        $title2 = TaskTitle::create('Same title')->unwrap();

        static::assertTrue($title1->equals($title2));
    }

    /**
     * @throws \Throwable
     */
    public function testNotEquals(): void
    {
        $title1 = TaskTitle::create('Title A')->unwrap();
        $title2 = TaskTitle::create('Title B')->unwrap();

        static::assertFalse($title1->equals($title2));
    }
}
