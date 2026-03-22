<?php

declare(strict_types=1);

namespace App\Domain\Task;

use EndouMame\PhpMonad\Result;

/** @api */
interface TaskRepositoryInterface
{
    /**
     * @return Result<Task, \Throwable>
     */
    public function findById(TaskId $id): Result;

    /**
     * @return Result<list<Task>, \Throwable>
     */
    public function findAll(): Result;

    /**
     * @return Result<Task, \Throwable>
     */
    public function save(Task $task): Result;

    /**
     * @return Result<true, \Throwable>
     */
    public function delete(TaskId $id): Result;
}
