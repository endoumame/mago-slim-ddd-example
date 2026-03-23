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

        // @mago-expect analysis:impossible-type-comparison
        self::assertTrue($result->isOk());
        self::assertSame('Buy groceries', $result->unwrap()->value());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateTrimsWhitespace(): void
    {
        $result = TaskTitle::create('  Buy groceries  ');

        // @mago-expect analysis:impossible-type-comparison
        self::assertTrue($result->isOk());
        self::assertSame('Buy groceries', $result->unwrap()->value());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithEmptyStringFails(): void
    {
        $result = TaskTitle::create('');

        // @mago-expect analysis:impossible-type-comparison
        self::assertTrue($result->isErr());
        self::assertInstanceOf(InvalidTaskTitleException::class, $result->unwrapErr());
        self::assertStringContainsString('empty', $result->unwrapErr()->getMessage());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithWhitespaceOnlyFails(): void
    {
        $result = TaskTitle::create('   ');

        // @mago-expect analysis:impossible-type-comparison
        self::assertTrue($result->isErr());
        self::assertInstanceOf(InvalidTaskTitleException::class, $result->unwrapErr());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithMaxLengthSucceeds(): void
    {
        $title = str_repeat(string: 'a', times: 255);
        $result = TaskTitle::create($title);

        // @mago-expect analysis:impossible-type-comparison
        self::assertTrue($result->isOk());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithTooLongTitleFails(): void
    {
        $title = str_repeat(string: 'a', times: 256);
        $result = TaskTitle::create($title);

        // @mago-expect analysis:impossible-type-comparison
        self::assertTrue($result->isErr());
        self::assertInstanceOf(InvalidTaskTitleException::class, $result->unwrapErr());
        self::assertStringContainsString('255', $result->unwrapErr()->getMessage());
    }

    /**
     * @throws \Throwable
     */
    public function testEquals(): void
    {
        $title1 = TaskTitle::create('Same title')->unwrap();
        $title2 = TaskTitle::create('Same title')->unwrap();

        self::assertTrue($title1->equals($title2));
    }

    /**
     * @throws \Throwable
     */
    public function testNotEquals(): void
    {
        $title1 = TaskTitle::create('Title A')->unwrap();
        $title2 = TaskTitle::create('Title B')->unwrap();

        self::assertFalse($title1->equals($title2));
    }
}
