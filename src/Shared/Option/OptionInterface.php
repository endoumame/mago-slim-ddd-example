<?php

declare(strict_types=1);

namespace App\Shared\Option;

use Closure;

/**
 * Represents an optional value: every Option is either Some and contains a value, or None.
 *
 * @template T
 */
interface OptionInterface
{
    public function isSome(): bool;

    public function isNone(): bool;

    /**
     * @return T
     *
     * @throws \RuntimeException if None
     */
    public function unwrap(): mixed;

    /**
     * @param T $default
     *
     * @return T
     */
    public function unwrapOr(mixed $default): mixed;

    /**
     * @template U
     *
     * @param (Closure(T): U) $fn
     *
     * @return OptionInterface<U>
     */
    public function map(Closure $fn): self;

    /**
     * @template U
     *
     * @param (Closure(T): OptionInterface<U>) $fn
     *
     * @return OptionInterface<U>
     */
    public function flatMap(Closure $fn): self;

    /**
     * @param (Closure(T): bool) $predicate
     *
     * @return OptionInterface<T>
     */
    public function filter(Closure $predicate): self;

    /**
     * @template U
     *
     * @param (Closure(T): U) $some
     * @param (Closure(): U) $none
     *
     * @return U
     */
    public function match(Closure $some, Closure $none): mixed;
}
