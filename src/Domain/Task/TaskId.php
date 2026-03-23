<?php

declare(strict_types=1);

namespace App\Domain\Task;

use App\Domain\Task\Exception\InvalidTaskIdException;
use EndouMame\PhpMonad\Result;
use Ramsey\Uuid\Uuid;

use function EndouMame\PhpMonad\Result\err;
use function EndouMame\PhpMonad\Result\ok;

/**
 * @psalm-immutable
 */
final readonly class TaskId
{
    private function __construct(
        private string $value,
    ) {}

    /**
     * Parse a string into a TaskId. Returns Err for invalid UUIDs.
     *
     * @return Result<TaskId, InvalidTaskIdException>
     */
    public static function create(string $value): Result
    {
        if (!Uuid::isValid($value)) {
            /** @var Result<TaskId, InvalidTaskIdException> */
            return err(InvalidTaskIdException::invalidFormat($value));
        }

        return ok(new self($value));
    }

    /**
     * Generate a new TaskId with a random UUID v4.
     */
    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
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
