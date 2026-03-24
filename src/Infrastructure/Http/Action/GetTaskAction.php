<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action;

use App\Application\Task\Handler\GetTaskHandler;
use App\Application\Task\Query\GetTaskQuery;
use App\Infrastructure\Http\JsonResponseFactory;
use Psr\Http\Message\ResponseInterface;

final readonly class GetTaskAction
{
    public function __construct(
        private GetTaskHandler $handler,
        private JsonResponseFactory $responseFactory,
    ) {}

    /**
     * @throws \Throwable
     */
    public function __invoke(string $id): ResponseInterface
    {
        return $this->responseFactory->fromTaskResult($this->handler->handle(new GetTaskQuery(id: $id)));
    }
}
