<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action;

use App\Application\Task\Get\TaskGetQuery;
use App\Application\Task\Get\TaskGetQueryHandler;
use App\Infrastructure\Http\JsonResponseFactory;
use Psr\Http\Message\ResponseInterface;

final readonly class GetTaskAction
{
    public function __construct(
        private TaskGetQueryHandler $handler,
    ) {}

    /**
     * @throws \Throwable
     */
    public function __invoke(string $id): ResponseInterface
    {
        return JsonResponseFactory::fromTaskResult($this->handler->handle(new TaskGetQuery(id: $id)));
    }
}
