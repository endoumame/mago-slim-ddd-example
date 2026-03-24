<?php

declare(strict_types=1);

namespace App\Shared\Option;

use Closure;
use EndouMame\PhpMonad\Option;
use EndouMame\PhpMonad\Result;

use function EndouMame\PhpMonad\Result\andThen;
use function EndouMame\PhpMonad\Result\ok;

/**
 * Convert an Option to a Result.
 *
 * Some($value) becomes ok($value).
 * None becomes err($error).
 *
 * @template T
 * @template E
 *
 * @param Option<T> $option
 * @param E $error
 *
 * @return Result<T, E>
 */
function ok_or(Option $option, mixed $error): Result
{
    return $option->okOr($error);
}

/**
 * Apply a function that returns Result if Some, or ok(null) if None.
 *
 * Useful for optional value object creation:
 *   traverse(fromValue($command->dueDate), fn(string $d) => DueDate::create($d))
 *
 * @template T
 * @template U
 * @template E
 *
 * @param Option<T> $option
 * @param (Closure(T): Result<U, E>) $fn
 *
 * @return Result<U|null, E>
 */
function traverse(Option $option, Closure $fn): Result
{
    return $option->mapOrElse(
        /** @param T $value */
        static fn(mixed $value): Result => $fn($value),
        static fn(): Result => ok(null),
    );
}

/**
 * Curried form of traverse for use with the pipeline operator (|>).
 *
 * Usage: $nullable |> fromValue(...) |> traverse_with(DueDate::create(...))
 *
 * @template T
 * @template U
 * @template E
 *
 * @param (Closure(T): Result<U, E>) $fn
 *
 * @return (Closure(Option<T>): Result<U|null, E>)
 */
function traverse_with(Closure $fn): Closure
{
    return static fn(Option $option): Result => traverse($option, $fn);
}

/**
 * Conditionally apply a bind operation based on an Option value.
 *
 * If Some, applies $fn(unwrapped value) which must return a Closure suitable for andThen().
 * If None, returns an identity function that passes Result through unchanged.
 *
 * Usage with pipeline operator:
 *   ok($task)
 *       |> apply_if_some(fromValue($title), fn(string $t) => fn(Task $task) => ...)
 *
 * @template T
 * @template V
 * @template W
 * @template E
 *
 * @param Option<T> $option
 * @param (Closure(T): (Closure(V): Result<W, E>)) $fn
 *
 * @return (Closure(Result<V, E>): Result<V|W, E>)
 */
function apply_if_some(Option $option, Closure $fn): Closure
{
    return $option->mapOrElse(
        static function (mixed $value) use ($fn): Closure {
            $binding = $fn($value);

            return andThen($binding);
        },
        static fn(): Closure => static fn(Result $result): Result => $result,
    );
}
