<?php


// todo: rid of hardcoded table and column names

class PostgresStorage implements Market
{
    private $db;
    public function __construct($customersDB, $executorsDB, $tasksDB)
    {
        $host           = "host = 127.0.0.1";
        $port           = "port = 5432";
        $dbname         = "dbname = customers";
        $credentials    = "user = market password=123";

        $this->db = pg_connect("$host $port $dbname $credentials");
        if(!$this->db)
        {
            // todo
            echo "error: cannot connect to db";
            die;
        }

        // todo: select balance type
        $query =<<<EOF
        CREATE TABLE IF NOT EXISTS customers (
          ID serial primary key,
          BALANCE numeric(16,0) 
        );
EOF;
        $ret = pg_query($this->db, $query);
        if(!$ret)
        {
            echo pg_last_error($this->db);
        }

    }

    public function __destruct()
    {
        pg_close($this->db);
    }

    // todo: this function mutates $customer. how would I show user this object mutates?
    public function CreateCustomer(Customer $customer)
    {
        $query =<<<EOF
            INSERT INTO customers (BALANCE)
            VALUES ({$customer->balance});
EOF;
        $ret = pg_query($this->db, $query);
        if(!$ret)
        {
            echo pg_last_error($this->db);
            die; // todo
        }
        /*
         * currval() returns the last value generated by the sequence for the current session.
         * So, no race conditions are possible.
         */
        $query =<<<EOF
            SELECT currval(pg_get_serial_sequence('customers', 'id'));
EOF;
        $ret = pg_query($this->db, $query);
        if(!$ret)
        {
            echo pg_last_error($this->db);
            die;
        }

        $ret = pg_fetch_result($ret, 0);
        $customer->id = intval($ret);
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