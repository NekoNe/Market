<?php

interface Market
{
    public function CreateCustomer(Customer $customer);
    public function ReadCustomer(string $id);
    public function UpdateCustomer(string $id, callable $updater);
    public function DeleteCustomer(string $id);
    public function ListCustomers(int $page, int $pageSize);

    public function CreateExecutor(Executor $executor);
    public function ReadExecutor(string $id);
    public function UpdateExecutor(string $id, callable $updater);
    public function DeleteExecutor(string $id);
    public function ListExecutors(int $page, int $pageSize);

    public function CreateTask(Customer $customer, Task $task);
    public function ExecuteTask(Executor $executor, Task $task);
    public function ListTasks(int $page, int $pageSize);
}

class MySQLMarket implements Market
{
    public function CreateCustomer(Customer $customer){}
    public function ReadCustomer(string $id){}
    public function UpdateCustomer(string $id, callable $updater){}
    public function DeleteCustomer(string $id){}
    public function ListCustomers(int $page, int $pageSize){}

    public function CreateExecutor(Executor $executor){}
    public function ReadExecutor(string $id){}
    public function UpdateExecutor(string $id, callable $updater){}
    public function DeleteExecutor(string $id){}
    public function ListExecutors(int $page, int $pageSize){}

    public function CreateTask(Customer $customer, Task $task){}
    public function ExecuteTask(Executor $executor, Task $task){}
    public function ListTasks(int $page, int $pageSize){}
}