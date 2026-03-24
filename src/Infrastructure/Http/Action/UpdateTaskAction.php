<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action;

use App\Application\Task\Update\TaskUpdateCommand;
use App\Application\Task\Update\TaskUpdateCommandHandler;
use App\Infrastructure\Http\JsonResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class UpdateTaskAction
{
    public function __construct(
        private TaskUpdateCommandHandler $handler,
    ) {}

    /**
     * @throws \Throwable
     */
    public function __invoke(ServerRequestInterface $request, string $id): ResponseInterface
    {
        /** @var array<string, mixed> */
        $body = $request->getParsedBody() ?? [];

        $command = new TaskUpdateCommand(
            id: $id,
            title: \array_key_exists('title', $body) ? (string) $body['title'] : null,
            description: \array_key_exists('description', $body) ? (string) $body['description'] : null,
            dueDate: \array_key_exists('due_date', $body) ? (string) $body['due_date'] : null,
        );

        return JsonResponseFactory::fromTaskResult($this->handler->handle($command));
    }
}
