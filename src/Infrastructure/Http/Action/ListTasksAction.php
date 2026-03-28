<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action;

use App\Application\Task\List\ListTasksHandler;
use App\Application\Task\List\ListTasksQuery;
use App\Infrastructure\Http\JsonResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ListTasksAction
{
    public function __construct(
        private ListTasksHandler $handler,
    ) {}

    /**
     * @throws \Throwable
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $query = new ListTasksQuery(
            status: \array_key_exists('status', $params) ? (string) $params['status'] : null,
            priority: \array_key_exists('priority', $params) ? (string) $params['priority'] : null,
        );

        return JsonResponseFactory::fromTaskListResult($this->handler->handle($query));
    }
}
