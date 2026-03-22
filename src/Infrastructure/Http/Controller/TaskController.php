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
use Psl\Result\ResultInterface;
use Psl\Vec;
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
     *
     */
    public function get(ServerRequestInterface $request, ResponseInterface $response, string $id): ResponseInterface
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

        $result = $this->listHandler->handle($query);

        if ($result->isFailed()) {
            return $this->errorResponse($result->getThrowable());
        }

        $tasks = $result->getResult();

        return $this->jsonResponse([
            'data' => Vec\map(
                $tasks,
                /** @return array<string, mixed> */
                static fn(Task $task): array => $task->toArray(),
            ),
        ]);
    }

    /**
     * @throws \Throwable
     *
     */
    public function update(ServerRequestInterface $request, ResponseInterface $response, string $id): ResponseInterface
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
     *
     */
    public function delete(ServerRequestInterface $request, ResponseInterface $response, string $id): ResponseInterface
    {
        $command = new DeleteTaskCommand(id: $id);
        $result = $this->deleteHandler->handle($command);

        if ($result->isFailed()) {
            return $this->errorResponse($result->getThrowable());
        }

        return $this->jsonResponse(['data' => null], 204);
    }

    /**
     * @throws \Throwable
     *
     */
    public function changeStatus(
        ServerRequestInterface $request,
        ResponseInterface $response,
        string $id,
    ): ResponseInterface {
        $body = $this->parsedBody($request);

        $command = new ChangeTaskStatusCommand(id: $id, status: (string) ($body['status'] ?? ''));

        return $this->toResponse($this->changeStatusHandler->handle($command));
    }

    /**
     * @param ResultInterface<Task> $result
     *
     * @throws \Throwable
     */
    private function toResponse(ResultInterface $result, int $successCode = 200): ResponseInterface
    {
        if ($result->isFailed()) {
            return $this->errorResponse($result->getThrowable());
        }

        $task = $result->getResult();

        return $this->jsonResponse(['data' => $task->toArray()], $successCode);
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
