<?php

declare(strict_types=1);

namespace App\Shared\Result;

use Closure;
use EndouMame\PhpMonad\Result;

/**
 * Railway-oriented programming utilities built on top of EndouMame\PhpMonad\Result.
 *
 * These functions enable chaining operations that return Result,
 * forming a "railway" where failures short-circuit through the pipeline.
 */

/**
 * Curried form of andThen for use with the pipeline operator (|>).
 *
 * Returns a closure that accepts a Result and applies andThen with the given $fn.
 *
 * Usage: $result |> bind(fn($value) => someOperation($value))
 *
 * @template T
 * @template U
 * @template E
 *
 * @param (Closure(T): Result<U, E>) $fn
 *
 * @return (Closure(Result<T, E>): Result<U, E>)
 */
function bind(Closure $fn): Closure
{
    return static fn(Result $result): Result => $result->andThen($fn);
}
