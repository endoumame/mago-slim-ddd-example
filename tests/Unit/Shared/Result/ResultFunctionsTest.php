<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Result;

use EndouMame\PhpMonad\Result;
use PHPUnit\Framework\TestCase;

use function App\Shared\Result\flat_map_all;
use function App\Shared\Result\map_all;
use function EndouMame\PhpMonad\Result\err;
use function EndouMame\PhpMonad\Result\ok;

final class ResultFunctionsTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testMapAllWithAllOkAppliesFunction(): void
    {
        $a = ok(1);
        $b = ok(2);
        $c = ok(3);

        $result = map_all(static fn(int $x, int $y, int $z): int => $x + $y + $z, $a, $b, $c);

        self::assertSame(6, $result->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testMapAllWithFirstErrReturnsFirstErr(): void
    {
        $a = err(new \InvalidArgumentException('first'));
        $b = ok(2);
        $c = err(new \RuntimeException('third'));

        $result = map_all(static fn(int $x, int $y, int $z): int => $x + $y + $z, $a, $b, $c);

        self::assertInstanceOf(\InvalidArgumentException::class, $result->unwrapErr());
        self::assertSame('first', $result->unwrapErr()->getMessage());
    }

    /**
     * @throws \Throwable
     */
    public function testMapAllWithSingleResult(): void
    {
        $result = map_all(static fn(int $x): string => "value: {$x}", ok(42));

        self::assertSame('value: 42', $result->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testFlatMapAllWithAllOkAppliesFunction(): void
    {
        $a = ok('hello');
        $b = ok('world');

        $result = flat_map_all(static fn(string $x, string $y): Result => ok("{$x} {$y}"), $a, $b);

        self::assertSame('hello world', $result->unwrap());
    }

    /**
     * @throws \Throwable
     */
    public function testFlatMapAllWithAllOkButCallbackReturnsErr(): void
    {
        $a = ok(1);
        $b = ok(2);

        $result = flat_map_all(
            static fn(int $_x, int $_y): Result => err(new \LogicException('callback failed')),
            $a,
            $b,
        );

        self::assertInstanceOf(\LogicException::class, $result->unwrapErr());
    }

    /**
     * @throws \Throwable
     */
    public function testFlatMapAllWithErrShortCircuits(): void
    {
        $a = ok(1);
        $b = err(new \RuntimeException('fail'));
        $c = ok(3);

        $callbackCalled = false;
        $result = flat_map_all(
            static function (int $x, int $y, int $z) use (&$callbackCalled): Result {
                $callbackCalled = true;

                return ok($x + $y + $z);
            },
            $a,
            $b,
            $c,
        );

        self::assertInstanceOf(\RuntimeException::class, $result->unwrapErr());
        self::assertFalse($callbackCalled);
    }

    /**
     * @throws \Throwable
     */
    public function testFlatMapAllReturnsFirstErr(): void
    {
        $a = err(new \InvalidArgumentException('first'));
        $b = err(new \RuntimeException('second'));

        $result = flat_map_all(static fn(int $x, int $y): Result => ok($x + $y), $a, $b);

        self::assertInstanceOf(\InvalidArgumentException::class, $result->unwrapErr());
    }
}
