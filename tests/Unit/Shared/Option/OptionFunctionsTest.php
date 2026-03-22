<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Option;

use EndouMame\PhpMonad\Option;
use EndouMame\PhpMonad\Result;
use PHPUnit\Framework\TestCase;

use function App\Shared\Option\apply_if_some;
use function App\Shared\Option\ok_or;
use function App\Shared\Option\traverse;
use function EndouMame\PhpMonad\Option\none;
use function EndouMame\PhpMonad\Option\some;
use function EndouMame\PhpMonad\Result\ok;

final class OptionFunctionsTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testOkOrConvertsNoneToErr(): void
    {
        $result = ok_or(none(), new \InvalidArgumentException('missing'));

        self::assertTrue($result->isErr());
        self::assertInstanceOf(\InvalidArgumentException::class, $result->unwrapErr());
    }

    /**
     * @throws \Throwable
     */
    public function testOkOrConvertsSomeToOk(): void
    {
        $result = ok_or(some(42), new \InvalidArgumentException('missing'));

        self::assertTrue($result->isOk());
        self::assertSame(42, $result->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testTraverseWithSomeAppliesFn(): void
    {
        $result = traverse(some('hello'), static fn(string $s): Result => ok(mb_strtoupper($s)));

        self::assertTrue($result->isOk());
        self::assertSame('HELLO', $result->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testTraverseWithNoneReturnsOkNull(): void
    {
        /** @var Option<string> */
        $none = none();
        $result = traverse($none, static fn(string $s): Result => ok(mb_strtoupper($s)));

        self::assertTrue($result->isOk());
        self::assertNull($result->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testApplyIfSomeWithSomeAppliesBindFunction(): void
    {
        $initial = ok(10);

        $fn = apply_if_some(
            some(5),
            static fn(int $extra): \Closure => static fn(int $current): Result => ok($current + $extra),
        );

        /** @var Result<int, never> $result */
        $result = $fn($initial);

        self::assertTrue($result->isOk());
        self::assertSame(15, $result->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testApplyIfSomeWithNonePassesThrough(): void
    {
        $initial = ok(10);

        /** @var Option<int> */
        $none = none();
        $fn = apply_if_some(
            $none,
            static fn(int $extra): \Closure => static fn(int $current): Result => ok($current + $extra),
        );

        /** @var Result<int, never> $result */
        $result = $fn($initial);

        self::assertTrue($result->isOk());
        self::assertSame(10, $result->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testApplyIfSomeChaining(): void
    {
        $initial = ok(0);

        $step1 = apply_if_some(some(1), static fn(int $v): \Closure => static fn(int $acc): Result => ok($acc + $v));

        $step2 = apply_if_some(none(), static fn(int $v): \Closure => static fn(int $acc): Result => ok($acc + $v));

        $step3 = apply_if_some(some(3), static fn(int $v): \Closure => static fn(int $acc): Result => ok($acc + $v));

        /** @var Result<int, never> $result */
        $result = $step3($step2($step1($initial)));

        self::assertTrue($result->isOk());
        self::assertSame(4, $result->unwrap());
    }
}
