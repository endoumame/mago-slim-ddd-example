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

        // @mago-expect analysis:impossible-type-comparison
        self::assertTrue($result->isOk());
        self::assertSame('A detailed task description', $result->unwrap()->value());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithEmptyStringSucceeds(): void
    {
        $result = TaskDescription::create('');

        // @mago-expect analysis:impossible-type-comparison
        self::assertTrue($result->isOk());
        self::assertSame('', $result->unwrap()->value());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateTrimsWhitespace(): void
    {
        $result = TaskDescription::create('  description  ');

        // @mago-expect analysis:impossible-type-comparison
        self::assertTrue($result->isOk());
        self::assertSame('description', $result->unwrap()->value());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithMaxLengthSucceeds(): void
    {
        $desc = str_repeat(string: 'a', times: 1000);
        $result = TaskDescription::create($desc);

        // @mago-expect analysis:impossible-type-comparison
        self::assertTrue($result->isOk());
    }

    /**
     * @throws \Throwable
     */
    public function testCreateWithTooLongDescriptionFails(): void
    {
        $desc = str_repeat(string: 'a', times: 1001);
        $result = TaskDescription::create($desc);

        // @mago-expect analysis:impossible-type-comparison
        self::assertTrue($result->isErr());
        self::assertInstanceOf(InvalidTaskDescriptionException::class, $result->unwrapErr());
        self::assertStringContainsString('1000', $result->unwrapErr()->getMessage());
    }

    /**
     * @throws \Throwable
     */
    public function testEmptyFactory(): void
    {
        $desc = TaskDescription::empty();

        self::assertSame('', $desc->value());
    }

    /**
     * @throws \Throwable
     */
    public function testEquals(): void
    {
        $desc1 = TaskDescription::create('Same')->unwrap();
        $desc2 = TaskDescription::create('Same')->unwrap();

        self::assertTrue($desc1->equals($desc2));
    }
}
