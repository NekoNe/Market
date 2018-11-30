<?php

interface Market
{
    public function CreateCustomer(Customer $customer);
    public function ReadCustomer(string $id): ?Customer;
    public function UpdateCustomer(string $id, callable $updater);
    public function DeleteCustomer(string $id);
    public function ListCustomers(int $offset, int $length): CustomerList;

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
    public function __construct($customersDB, $executorsDB, $tasksDB)
    {
    }

    public function CreateCustomer(Customer $customer)
    {

    }

    public function ReadCustomer(string $id): ?Customer
    {
        //return null;
        return new Customer(); // todo
    }

    public function UpdateCustomer(string $id, callable $updater)
    {

    }

    public function DeleteCustomer(string $id)
    {

    }

    public function ListCustomers(int $offset, int $length): CustomerList {
        $list = new CustomerList();
        $list->addCustomer(new Customer()); // todo
        return $list;
    }

    public function CreateExecutor(Executor $executor){}
    public function ReadExecutor(string $id){}
    public function UpdateExecutor(string $id, callable $updater){}
    public function DeleteExecutor(string $id){}
    public function ListExecutors(int $page, int $pageSize){}

    public function CreateTask(Customer $customer, Task $task){}
    public function ExecuteTask(Executor $executor, Task $task){}
    public function ListTasks(int $page, int $pageSize){}
}