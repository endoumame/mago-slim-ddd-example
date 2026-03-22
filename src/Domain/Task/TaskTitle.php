<?php

declare(strict_types=1);

namespace App\Domain\Task;

use App\Domain\Task\Exception\InvalidTaskTitleException;
use Psl\Result\ResultInterface;
use Psl\Str;

use function App\Shared\Result\fail;
use function App\Shared\Result\succeed;

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
     * Parse a string into a TaskTitle. Returns Failure for invalid values.
     *
     * @return ResultInterface<TaskTitle>
     *
     * @throws Str\Exception\InvalidArgumentException
     */
    public static function create(string $value): ResultInterface
    {
        $trimmed = Str\trim($value);

        if ($trimmed === '') {
            /** @var ResultInterface<TaskTitle> */
            return fail(InvalidTaskTitleException::empty());
        }

        $length = Str\length($trimmed);
        if ($length > self::MAX_LENGTH) {
            /** @var ResultInterface<TaskTitle> */
            return fail(InvalidTaskTitleException::tooLong($length));
        }

        return succeed(new self($trimmed));
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
