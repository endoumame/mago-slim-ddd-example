<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action;

use App\Application\Task\Command\CreateTaskCommand;
use App\Application\Task\Handler\CreateTaskHandler;
use App\Infrastructure\Http\JsonResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class CreateTaskAction
{
    public function __construct(
        private CreateTaskHandler $handler,
        private JsonResponseFactory $responseFactory,
    ) {}

    /**
     * @throws \Throwable
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        /** @var array<string, mixed> */
        $body = $request->getParsedBody() ?? [];

        $command = new CreateTaskCommand(
            title: \array_key_exists('title', $body) ? (string) $body['title'] : '',
            description: \array_key_exists('description', $body) ? (string) $body['description'] : '',
            dueDate: \array_key_exists('due_date', $body) ? (string) $body['due_date'] : null,
        );

        return $this->responseFactory->fromTaskResult($this->handler->handle($command), 201);
    }
}
