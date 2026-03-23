<?php

declare(strict_types=1);

namespace App\Tests\Support;

use Eris\Generator;
use Eris\Generator\ChooseGenerator;
use Eris\Generator\ConstantGenerator;
use Eris\Generator\MapGenerator;
use Eris\Generator\StringGenerator;
use Eris\Generator\SuchThatGenerator;
use Eris\Quantifier\ForAll;
use Eris\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Base class for property-based tests using Eris.
 *
 * Provides properly-typed wrappers around Eris's generator functions and
 * `forAll()` method. The originals lack return types and use `func_get_args()`,
 * which confuses static analyzers.
 *
 * @internal
 */
abstract class PropertyTestCase extends TestCase
{
    use TestTrait {
        TestTrait::forAll as private traitForAll;
    }

    /**
     * @param Generator<mixed> ...$generators
     */
    public function forAll(Generator ...$generators): ForAll
    {
        /** @var ForAll */
        return call_user_func_array([$this, 'traitForAll'], $generators);
    }

    /**
     * @return Generator<string>
     */
    protected static function string(): Generator
    {
        return new StringGenerator();
    }

    /**
     * @template T
     *
     * @param callable(T): bool $filter
     * @param Generator<T> $generator
     *
     * @return Generator<T>
     */
    protected static function suchThat(callable $filter, Generator $generator, int $maximumAttempts = 100): Generator
    {
        return new SuchThatGenerator($filter, $generator, $maximumAttempts);
    }

    /**
     * @template T
     * @template U
     *
     * @param callable(T): U $function
     * @param Generator<T> $generator
     *
     * @return Generator<U>
     */
    protected static function map(callable $function, Generator $generator): Generator
    {
        return new MapGenerator($function, $generator);
    }

    /**
     * @return Generator<int>
     */
    protected static function choose(int $lowerLimit, int $upperLimit): Generator
    {
        return new ChooseGenerator($lowerLimit, $upperLimit);
    }

    /**
     * @template T
     *
     * @param T $value
     *
     * @return Generator<T>
     */
    protected static function constant(mixed $value): Generator
    {
        return new ConstantGenerator($value);
    }
}
