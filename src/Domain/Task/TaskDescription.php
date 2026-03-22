<?php

declare(strict_types=1);

namespace App\Domain\Task;

use App\Domain\Task\Exception\InvalidTaskDescriptionException;
use EndouMame\PhpMonad\Result;

use function EndouMame\PhpMonad\Result\err;
use function EndouMame\PhpMonad\Result\ok;

/**
 * @psalm-immutable
 */
final readonly class TaskDescription
{
    private const int MAX_LENGTH = 1000;

    private function __construct(
        private string $value,
    ) {}

    /**
     * Parse a string into a TaskDescription. Returns Err for invalid values.
     *
     * @return Result<TaskDescription, InvalidTaskDescriptionException>
     */
    public static function create(string $value): Result
    {
        $trimmed = trim($value);
        $length = mb_strlen($trimmed);

        if ($length > self::MAX_LENGTH) {
            /** @var Result<TaskDescription, InvalidTaskDescriptionException> */
            return err(InvalidTaskDescriptionException::tooLong($length));
        }

        return ok(new self($trimmed));
    }

    /**
     * Create an empty description.
     */
    public static function empty(): self
    {
        return new self('');
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
