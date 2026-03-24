<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Domain\Task\Task;
use EndouMame\PhpMonad\Result;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;

/**
 * @internal
 */
final readonly class JsonResponseFactory
{
    /**
     * @param array<string, mixed> $data
     *
     * @throws \JsonException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public static function json(array $data, int $statusCode = 200): ResponseInterface
    {
        $response = new Response($statusCode)->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(\json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));

        return $response;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function noContent(): ResponseInterface
    {
        return new Response(204)->withHeader('Content-Type', 'application/json');
    }

    /**
     * @throws \JsonException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public static function fromError(\Throwable $error): ResponseInterface
    {
        [$statusCode, $type] = DomainErrorMapper::map($error);

        return self::json(['error' => ['type' => $type, 'message' => $error->getMessage()]], $statusCode);
    }

    /**
     * @param Result<Task, \Throwable> $result
     *
     * @throws \Throwable
     */
    public static function fromTaskResult(Result $result, int $successCode = 200): ResponseInterface
    {
        if ($result->isErr()) {
            return self::fromError($result->unwrapErr());
        }

        return self::json(['data' => $result->unwrap()->toArray()], $successCode);
    }

    /**
     * @param Result<list<Task>, \Throwable> $result
     *
     * @throws \Throwable
     */
    public static function fromTaskListResult(Result $result): ResponseInterface
    {
        if ($result->isErr()) {
            return self::fromError($result->unwrapErr());
        }

        return self::json([
            'data' => \array_map(
                /** @return array<string, mixed> */
                static fn(Task $task): array => $task->toArray(),
                $result->unwrap(),
            ),
        ]);
    }

    /**
     * @param Result<true, \Throwable> $result
     *
     * @throws \Throwable
     */
    public static function fromDeleteResult(Result $result): ResponseInterface
    {
        if ($result->isErr()) {
            return self::fromError($result->unwrapErr());
        }

        return self::noContent();
    }
}
