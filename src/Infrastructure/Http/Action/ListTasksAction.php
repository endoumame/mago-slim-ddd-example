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
        $overdue = \array_key_exists('overdue', $params)
            ? \filter_var($params['overdue'], \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE)
            : null;
        $query = new ListTasksQuery(
            status: \array_key_exists('status', $params) ? (string) $params['status'] : null,
            priority: \array_key_exists('priority', $params) ? (string) $params['priority'] : null,
            overdue: $overdue,
            sortBy: \array_key_exists('sort_by', $params) ? (string) $params['sort_by'] : null,
            sortDirection: \array_key_exists('sort_direction', $params) ? (string) $params['sort_direction'] : null,
        );

        return JsonResponseFactory::fromTaskListResult($this->handler->handle($query));
    }
}
