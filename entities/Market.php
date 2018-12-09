<?php

// todo: select appropriate isolation levels


class Market
{
    private $customers;
    private $tasks;
    private $executors;

    private $fee = 30; // percents

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
        // todo: delete all related tasks in transaction
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
        $this->customers->BeginTransaction();
        $this->tasks->BeginTransaction();

        $customer = $this->customers->Read($cid);
        $task->customerId = $customer->id;
        $this->tasks->Create($task);

        $this->tasks->CommitTransaction();
        $this->customers->CommitTransaction();
    }
    public function ExecuteTask(string $eid, string $tid)
    {
        $this->executors->BeginTransaction();
        $this->tasks->BeginTransaction();
        $this->customers->BeginTransaction();

        $executor = $this->executors->Read($eid);
        $task = $this->tasks->Read($tid);
        $customer = $this->customers->Read($task->customerId);

        if($customer->balance < $task->value)
        {
            throw new LowBalanceException("Customer {$customer->id} is too low to pay for this task");
        }

        $executorShare = ($task->value * (100 - $this->fee)) / 100;
        $marketShare = $task->value - $executorShare;

        $this->customers->Update($customer->id, function() use ($customer, $task){
            $updatedCustomer = clone $customer;
            $updatedCustomer->balance -= $task->value;
            return $updatedCustomer;
        });
        $this->executors->Update($executor->id, function() use ($executor, $task, $executorShare){
            $updatedExecutor = clone $executor;
            $updatedExecutor->balance += $executorShare;
            return $updatedExecutor;
        });
        $this->tasks->Delete($task->id);

        $this->customers->CommitTransaction();
        $this->tasks->CommitTransaction();
        $this->executors->CommitTransaction();
    }
}

