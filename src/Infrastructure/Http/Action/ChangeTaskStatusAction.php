<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action;

use App\Application\Task\Command\ChangeTaskStatusCommand;
use App\Application\Task\Handler\ChangeTaskStatusHandler;
use App\Infrastructure\Http\JsonResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ChangeTaskStatusAction
{
    public function __construct(
        private ChangeTaskStatusHandler $handler,
        private JsonResponseFactory $responseFactory,
    ) {}

    /**
     * @throws \Throwable
     */
    public function __invoke(ServerRequestInterface $request, string $id): ResponseInterface
    {
        /** @var array<string, mixed> */
        $body = $request->getParsedBody() ?? [];

        $command = new ChangeTaskStatusCommand(
            id: $id,
            status: \array_key_exists('status', $body) ? (string) $body['status'] : '',
        );

        return $this->responseFactory->fromTaskResult($this->handler->handle($command));
    }
}
