<?php

declare(strict_types=1);

namespace App\Domain\Task;

use Psl\Result\ResultInterface;

/** @api */
interface TaskRepositoryInterface
{
    /**
     * @return ResultInterface<Task>
     */
    public function findById(TaskId $id): ResultInterface;

    /**
     * @return ResultInterface<list<Task>>
     */
    public function findAll(): ResultInterface;

    /**
     * @return ResultInterface<Task>
     */
    public function save(Task $task): ResultInterface;

    /**
     * @return ResultInterface<true>
     */
    public function delete(TaskId $id): ResultInterface;
}
