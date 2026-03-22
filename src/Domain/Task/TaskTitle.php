<?php

declare(strict_types=1);

namespace App\Domain\Task;

use App\Domain\Task\Exception\InvalidTaskTitleException;
use EndouMame\PhpMonad\Result;

use function EndouMame\PhpMonad\Result\err;
use function EndouMame\PhpMonad\Result\ok;

/**
 * @psalm-immutable
 */
final readonly class TaskTitle
{
    private const int MAX_LENGTH = 255;

    private function __construct(
        private string $value,
    ) {}

    /**
     * Parse a string into a TaskTitle. Returns Err for invalid values.
     *
     * @return Result<TaskTitle, InvalidTaskTitleException>
     */
    public static function create(string $value): Result
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            /** @var Result<TaskTitle, InvalidTaskTitleException> */
            return err(InvalidTaskTitleException::empty());
        }

        $length = mb_strlen($trimmed);
        if ($length > self::MAX_LENGTH) {
            /** @var Result<TaskTitle, InvalidTaskTitleException> */
            return err(InvalidTaskTitleException::tooLong($length));
        }

        return ok(new self($trimmed));
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
