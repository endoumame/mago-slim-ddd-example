<?php

declare(strict_types=1);

namespace App\Shared\Option;

use Closure;

/**
 * Contains no value.
 *
 * @implements OptionInterface<never>
 */
final readonly class None implements OptionInterface
{
    public function isSome(): bool
    {
        return false;
    }

    public function isNone(): bool
    {
        return true;
    }

    public function unwrap(): never
    {
        throw new \RuntimeException('Called unwrap() on a None value.');
    }

    public function unwrapOr(mixed $default): mixed
    {
        return $default;
    }

    public function map(Closure $fn): OptionInterface
    {
        /** @var OptionInterface<never> */
        return $this;
    }

    public function flatMap(Closure $fn): OptionInterface
    {
        /** @var OptionInterface<never> */
        return $this;
    }

    public function filter(Closure $predicate): OptionInterface
    {
        /** @var OptionInterface<never> */
        return $this;
    }

    public function match(Closure $some, Closure $none): mixed
    {
        return $none();
    }
}
