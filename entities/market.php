<?php

// todo: decompose Market interface into CustomerStorage, ExecutorStorage, TaskStorage
interface Market
{
    public function CreateCustomer(Customer $customer);
    public function ReadCustomer(string $id): ?Task;
    public function UpdateCustomer(string $id, callable $updater);
    public function DeleteCustomer(string $id);
    public function ListCustomers(int $offset, int $length): CustomerList;

    public function CreateExecutor(Executor $executor);
    public function ReadExecutor(string $id): ?Executor;
    public function UpdateExecutor(string $id, callable $updater);
    public function DeleteExecutor(string $id);
    public function ListExecutors(int $page, int $pageSize): ExecutorList;

    public function CreateTask(string $cid, Task $task);
    public function ReadTask(string $tid): ?Task;
    public function ExecuteTask(string $eid, string $tid);
    public function ListTasks(int $offset, int $length): TaskList;
}

