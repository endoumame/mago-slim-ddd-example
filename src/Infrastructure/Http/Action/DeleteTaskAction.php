<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action;

use App\Application\Task\Delete\TaskDeleteCommand;
use App\Application\Task\Delete\TaskDeleteCommandHandler;
use App\Infrastructure\Http\JsonResponseFactory;
use Psr\Http\Message\ResponseInterface;

final readonly class DeleteTaskAction
{
    public function __construct(
        private TaskDeleteCommandHandler $handler,
    ) {}

    /**
     * @throws \Throwable
     */
    public function __invoke(string $id): ResponseInterface
    {
        return JsonResponseFactory::fromDeleteResult($this->handler->handle(new TaskDeleteCommand(id: $id)));
    }
}
