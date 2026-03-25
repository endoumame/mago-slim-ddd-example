<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action;

use App\Application\Task\Get\GetTask;
use App\Application\Task\Get\GetTaskHandler;
use App\Infrastructure\Http\JsonResponseFactory;
use Psr\Http\Message\ResponseInterface;

final readonly class GetTaskAction
{
    public function __construct(
        private GetTaskHandler $handler,
    ) {}

    /**
     * @throws \Throwable
     */
    public function __invoke(string $id): ResponseInterface
    {
        return JsonResponseFactory::fromTaskResult($this->handler->handle(new GetTask(id: $id)));
    }
}
