<?php

// todo: decompose Market interface into CustomerStorage, ExecutorStorage, TaskStorage


class Market
{
    private $customers;
    private $tasks;
    private $executors;

    public function __construct(CustomersStorage $customerStorage, ?TasksStorage $tasksStorage, ?ExecutorsStorage $executorsStorage)
    {
        $this->customers = $customerStorage;
        $this->tasks = $tasksStorage;
        $this->executors = $executorsStorage;
    }

    public function CreateCustomer(User $customer)
    {
        $this->customers->Create($customer);
    }
    public function ReadCustomer(string $id): ?User
    {
        return $this->customers->Read($id);
    }
    public function UpdateCustomer(string $id, callable $updater): ?User
    {
        return $this->customers->Update($id, $updater);
    }
    public function DeleteCustomer(string $id)
    {
        $this->customers->Delete($id);
    }
    public function ListCustomers(int $offset, int $length): UsersList
    {
        return $this->customers->List($offset, $length);
    }

    /**************************************************************************/

    public function CreateExecutor(Executor $executor)
    {
        $this->executors->Create($executor);
    }
    public function ReadExecutor(string $id): ?User
    {
        return $this->executors->Read($id);
    }
    //public function UpdateExecutor(string $id, callable $updater)
    //{
    //    $this->executors->Update($id, $updater);
    //}
    public function DeleteExecutor(string $id)
    {
        $this->executors->Delete($id);
    }
    public function ListExecutors(int $offset, int $length): UsersList
    {
        return $this->executors->List($offset, $length);
    }

    /**************************************************************************/

    public function ReadTask(string $tid): ?Task
    {
        return $this->tasks->Read($tid);
    }
    public function ListTasks(int $offset, int $length): TasksList
    {
        return $this->tasks->List($offset, $length);
    }
    public function CreateTask(string $cid, Task $task)
    {
        // todo: Implement CreateTask() method
    }
    public function ExecuteTask(string $eid, string $tid)
    {
        // todo: Implement ExecuteTask() method
    }
}

