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

class MySQLMarket implements Market
{
    public function __construct($customersDB, $executorsDB, $tasksDB)
    {
    }

    public function CreateCustomer(Customer $customer)
    {
    }
    public function ReadCustomer(string $id): ?Task
    {
        //return null;
        return new Task(); // todo
    }
    public function UpdateCustomer(string $id, callable $updater)
    {
    }
    public function DeleteCustomer(string $id)
    {
    }
    public function ListCustomers(int $offset, int $length): CustomerList
    {
        $list = new CustomerList();
        $list->addCustomer(new Customer()); // todo
        return $list;
    }

    public function CreateExecutor(Executor $executor)
    {
    }
    public function ReadExecutor(string $id): ?Executor
    {
        return new Executor(); // todo
    }
    public function UpdateExecutor(string $id, callable $updater)
    {
    }
    public function DeleteExecutor(string $id)
    {
    }
    public function ListExecutors(int $offset, int $length): ExecutorList
    {
        $list = new ExecutorList();
        $list->addExecutor(new Executor()); // todo
        return $list;
    }

    public function CreateTask(string $cid, Task $task){}
    public function ReadTask(string $tid): ?Task
    {
        return null; // todo;
    }

    public function ExecuteTask(string $eid, string $tid){}
    public function ListTasks(int $offset, int $length): TaskList
    {
        return new TaskList(); // todo
    }
}