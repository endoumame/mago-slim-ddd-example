<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controller;

use App\Application\Task\Command\ChangeTaskStatusCommand;
use App\Application\Task\Command\CreateTaskCommand;
use App\Application\Task\Command\DeleteTaskCommand;
use App\Application\Task\Command\UpdateTaskCommand;
use App\Application\Task\Handler\ChangeTaskStatusHandler;
use App\Application\Task\Handler\CreateTaskHandler;
use App\Application\Task\Handler\DeleteTaskHandler;
use App\Application\Task\Handler\GetTaskHandler;
use App\Application\Task\Handler\ListTasksHandler;
use App\Application\Task\Handler\UpdateTaskHandler;
use App\Application\Task\Query\GetTaskQuery;
use App\Application\Task\Query\ListTasksQuery;
use App\Domain\Task\Exception\DomainError;
use App\Domain\Task\Exception\TaskNotFoundException;
use App\Domain\Task\Task;
use EndouMame\PhpMonad\Result;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

final readonly class TaskController
{
    public function __construct(
        private CreateTaskHandler $createHandler,
        private UpdateTaskHandler $updateHandler,
        private DeleteTaskHandler $deleteHandler,
        private GetTaskHandler $getHandler,
        private ListTasksHandler $listHandler,
        private ChangeTaskStatusHandler $changeStatusHandler,
    ) {}

    /**
     * @throws \Throwable
     */
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        $body = $this->parsedBody($request);

        $command = new CreateTaskCommand(
            title: (string) ($body['title'] ?? ''),
            description: (string) ($body['description'] ?? ''),
            dueDate: isset($body['due_date']) ? (string) $body['due_date'] : null,
        );

        return $this->toResponse($this->createHandler->handle($command), 201);
    }

    /**
     * @throws \Throwable
     */
    public function get(string $id): ResponseInterface
    {
        $query = new GetTaskQuery(id: $id);

        return $this->toResponse($this->getHandler->handle($query));
    }

    /**
     * @throws \Throwable
     */
    public function list(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $query = new ListTasksQuery(status: isset($params['status']) ? (string) $params['status'] : null);

        return $this->handleResult(
            $this->listHandler->handle($query),
            /**
             * @param list<Task> $tasks
             * @return array<string, mixed>
             */
            static fn(array $tasks): array => [
                'data' => array_map(
                    /** @return array<string, mixed> */
                    static fn(Task $task): array => $task->toArray(),
                    $tasks,
                ),
            ],
        );
    }

    /**
     * @throws \Throwable
     */
    public function update(ServerRequestInterface $request, string $id): ResponseInterface
    {
        $body = $this->parsedBody($request);

        $command = new UpdateTaskCommand(
            id: $id,
            title: isset($body['title']) ? (string) $body['title'] : null,
            description: isset($body['description']) ? (string) $body['description'] : null,
            dueDate: isset($body['due_date']) ? (string) $body['due_date'] : null,
        );

        return $this->toResponse($this->updateHandler->handle($command));
    }

    /**
     * @throws \Throwable
     */
    public function delete(string $id): ResponseInterface
    {
        $command = new DeleteTaskCommand(id: $id);

        return $this->handleResult(
            $this->deleteHandler->handle($command),
            /** @return array<string, mixed> */
            static fn(mixed $_): array => ['data' => null],
            204,
        );
    }

    /**
     * @throws \Throwable
     */
    public function changeStatus(ServerRequestInterface $request, string $id): ResponseInterface
    {
        $body = $this->parsedBody($request);

        $command = new ChangeTaskStatusCommand(id: $id, status: (string) ($body['status'] ?? ''));

        return $this->toResponse($this->changeStatusHandler->handle($command));
    }

    /**
     * @param Result<Task, \Throwable> $result
     *
     * @throws \Throwable
     */
    private function toResponse(Result $result, int $successCode = 200): ResponseInterface
    {
        return $this->handleResult(
            $result,
            /** @return array<string, mixed> */
            static fn(Task $task): array => ['data' => $task->toArray()],
            $successCode,
        );
    }

    /**
     * @template T
     *
     * @param Result<T, \Throwable> $result
     * @param \Closure(T): array<string, mixed> $onSuccess
     *
     * @throws \Throwable
     */
    private function handleResult(Result $result, \Closure $onSuccess, int $successCode = 200): ResponseInterface
    {
        if ($result->isErr()) {
            return $this->errorResponse($result->unwrapErr());
        }

        return $this->jsonResponse($onSuccess($result->unwrap()), $successCode);
    }

    /**
     * @throws \JsonException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    private function errorResponse(\Throwable $error): ResponseInterface
    {
        [$statusCode, $type] = match (true) {
            $error instanceof TaskNotFoundException => [404, 'not_found'],
            $error instanceof DomainError, $error instanceof \InvalidArgumentException => [422, 'validation_error'],
            default => [500, 'internal_error'],
        };

        return $this->jsonResponse([
            'error' => [
                'type' => $type,
                'message' => $error->getMessage(),
            ],
        ], $statusCode);
    }

    /**
     * @return array<string, mixed>
     */
    private function parsedBody(ServerRequestInterface $request): array
    {
        /** @var array<string, mixed> */
        return $request->getParsedBody() ?? [];
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws \JsonException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    private function jsonResponse(array $data, int $statusCode = 200): ResponseInterface
    {
        $response = new Response($statusCode);
        $response = $response->withHeader('Content-Type', 'application/json');

        if ($statusCode !== 204) {
            $json = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            $response->getBody()->write($json);
        }

        return $response;
    }
}
