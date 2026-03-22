<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Option;

use EndouMame\PhpMonad\Option;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function EndouMame\PhpMonad\Option\fromValue;
use function EndouMame\PhpMonad\Option\none;
use function EndouMame\PhpMonad\Option\some;

final class OptionTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testSomeIsSome(): void
    {
        $option = some(42);

        self::assertTrue($option->isSome());
        self::assertFalse($option->isNone());
    }

    /**
     * @throws \Throwable
     */
    public function testNoneIsNone(): void
    {
        $option = none();

        self::assertTrue($option->isNone());
        self::assertFalse($option->isSome());
    }

    /**
     * @throws \Throwable
     */
    public function testFromValueWithValueReturnsSome(): void
    {
        $option = fromValue('hello');

        self::assertTrue($option->isSome());
        self::assertSame('hello', $option->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testFromValueWithNullReturnsNone(): void
    {
        $option = fromValue(null);

        self::assertTrue($option->isNone());
    }

    /**
     * @throws \Throwable
     */
    public function testUnwrapReturnsSomeValue(): void
    {
        self::assertSame(42, some(42)->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testUnwrapThrowsOnNone(): void
    {
        $this->expectException(RuntimeException::class);
        none()->unwrap();
    }

    /**
     * @throws \Throwable
     */
    public function testUnwrapOrReturnsSomeValue(): void
    {
        self::assertSame(42, some(42)->unwrapOr(99));
    }

    /**
     * @throws \Throwable
     */
    public function testUnwrapOrReturnsDefaultOnNone(): void
    {
        self::assertSame(99, none()->unwrapOr(99));
    }

    /**
     * @throws \Throwable
     */
    public function testMapTransformsSome(): void
    {
        $result = some(5)->map(static fn(int $v): int => $v * 2);

        self::assertTrue($result->isSome());
        self::assertSame(10, $result->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testMapSkipsNone(): void
    {
        /** @var Option<int> */
        $none = none();
        $result = $none->map(static fn(int $v): int => $v * 2);

        self::assertTrue($result->isNone());
    }

    /**
     * @throws \Throwable
     */
    public function testAndThenChainsSome(): void
    {
        $result = some(5)->andThen(static fn(int $v): Option => some($v * 2));

        self::assertTrue($result->isSome());
        self::assertSame(10, $result->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testAndThenReturnsNoneWhenClosureReturnsNone(): void
    {
        $result = some(5)->andThen(static fn(int $_): Option => none());

        self::assertTrue($result->isNone());
    }

    /**
     * @throws \Throwable
     */
    public function testAndThenSkipsNone(): void
    {
        /** @var Option<int> */
        $none = none();
        $result = $none->andThen(static fn(int $v): Option => some($v * 2));

        self::assertTrue($result->isNone());
    }

    /**
     * @throws \Throwable
     */
    public function testFilterKeepsSomeWhenPredicateTrue(): void
    {
        $result = some(10)->filter(static fn(int $v): bool => $v > 5);

        self::assertTrue($result->isSome());
        self::assertSame(10, $result->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testFilterDropsSomeWhenPredicateFalse(): void
    {
        $result = some(3)->filter(static fn(int $v): bool => $v > 5);

        self::assertTrue($result->isNone());
    }

    /**
     * @throws \Throwable
     */
    public function testFilterSkipsNone(): void
    {
        /** @var Option<int> */
        $none = none();
        $result = $none->filter(static fn(int $v): bool => $v > 5);

        self::assertTrue($result->isNone());
    }

    /**
     * @throws \Throwable
     */
    public function testMapOrElseCallsSomeOnSome(): void
    {
        $result = some(42)->mapOrElse(static fn(int $v): string => "value: {$v}", static fn(): string => 'empty');

        self::assertSame('value: 42', $result);
    }

    /**
     * @throws \Throwable
     */
    public function testMapOrElseCallsNoneOnNone(): void
    {
        /** @var Option<int> */
        $none = none();
        $result = $none->mapOrElse(static fn(int $v): string => "value: {$v}", static fn(): string => 'empty');

        self::assertSame('empty', $result);
    }

    /**
     * @throws \Throwable
     */
    public function testSomeInstanceOf(): void
    {
        $option = some(1);
        self::assertInstanceOf(Option::class, $option);
    }

    /**
     * @throws \Throwable
     */
    public function testNoneInstanceOf(): void
    {
        $option = none();
        self::assertInstanceOf(Option::class, $option);
    }
}
