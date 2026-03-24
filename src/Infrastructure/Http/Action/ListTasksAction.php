<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Action;

use App\Application\Task\List\TaskListQuery;
use App\Application\Task\List\TaskListQueryHandler;
use App\Infrastructure\Http\JsonResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ListTasksAction
{
    public function __construct(
        private TaskListQueryHandler $handler,
    ) {}

    /**
     * @throws \Throwable
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $query = new TaskListQuery(status: \array_key_exists('status', $params) ? (string) $params['status'] : null);

        return JsonResponseFactory::fromTaskListResult($this->handler->handle($query));
    }
}
