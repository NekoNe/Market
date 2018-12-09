<?php

include_once __DIR__ ."/../TasksStorage.php";

class TasksMySqlStorage implements TasksStorage
{
    private $db;
    private $tableName;

    private $idField = "id";
    private $valueField = "value";
    private $customerIdField = "customer";

    public function __construct(MySQLConfig $config, string $tableName)
    {
        $this->tableName = $tableName;

        $this->db = mysqli_connect($config->host, $config->user, $config->password, $config->dbname, $config->port);
        if(!$this->db)
        {
            throw new DatabaseException(mysqli_connect_error());
        }
        $query =<<<EOF
        CREATE TABLE IF NOT EXISTS {$this->tableName} (
          {$this->idField} BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
          {$this->customerIdField} integer,
          {$this->valueField} BIGINT UNSIGNED
        );
EOF;
        $ret = mysqli_query($this->db, $query);
        if(!$ret)
        {
            throw new DatabaseException(mysqli_error($this->db));
        }
    }

    public function __destruct()
    {
        mysqli_close($this->db);
    }

    public function Create(Task $task)
    {
        $query =<<<EOF
        INSERT INTO {$this->tableName} ({$this->customerIdField},{$this->valueField})
        VALUES ({$task->customerId}, {$task->value});
EOF;
        $ret = mysqli_query($this->db, $query);
        if(!$ret)
        {
            throw new DatabaseException(mysqli_error($this->db));
        }
        $id = mysqli_insert_id($this->db);
        if($id == 0)
        {
           throw new DatabaseException("mysqli: no new autoincrement value");
        }
        $task->id = $id;
    }

    public function Read(string $tid): ?Task
    {
        $query =<<<EOF
        SELECT * FROM {$this->tableName} WHERE {$this->idField} = {$tid};
EOF;
        $ret = mysqli_query($this->db, $query);
        if(!$ret)
        {
            throw new DatabaseException(mysqli_error($this->db));
        }
        $numRows = mysqli_num_rows($ret);
        if($numRows != 1)
        {   // todo: handle numRows > 1 and numRows == 0
            throw new ObjectNotFoundException("Task:{$tid}");
        }
        $obj = mysqli_fetch_object($ret);
        if(!$obj)
        {
            throw new DatabaseException(mysqli_error($this->db));
        }
        $task = new Task();
        $task->id = $obj->{$this->idField};
        $task->customerId = $obj->{$this->customerIdField};
        $task->value = $obj->{$this->valueField};
        return $task;
    }

    public function Delete(string $tid)
    {
        $query =<<<EOF
        DELETE FROM {$this->tableName}
        WHERE {$this->idField} = {$tid};
EOF;
        $ret = mysqli_query($this->db, $query);
        if(!$ret)
        {
            throw new DatabaseException(mysqli_error($this->db));
        }
        $deleted = mysqli_affected_rows($this->db);
        if($deleted == 0)
        {
            throw new ObjectNotFoundException("Task:{$tid}");
        }
        if($deleted > 1)
        {
            throw new MarketRuntimeException("Task DELETE:{$tid} affected rows {$deleted}");
        }
    }

    public function List(int $offset, int $length): TasksList
    {
        $query =<<<EOF
        SELECT * FROM {$this->tableName} LIMIT {$offset}, {$length};
EOF;
        $ret = mysqli_query($this->db, $query);
        if(!$ret)
        {
            throw new DatabaseException(mysqli_error($this->db));
        }
        $list = new TasksList();
        while($obj = mysqli_fetch_object($ret))
        {
            $task = new Task();
            $task->id = $obj->{$this->idField};
            $task->customerId = $obj->{$this->customerIdField};
            $task->value = $obj->{$this->valueField};

            $list->addTask($task);
        }
        return $list;
    }

    public function BeginTransaction()
    {
        $ret = mysqli_query($this->db, "START TRANSACTION;");
        if(!$ret)
        {
            throw new DatabaseException(mysqli_error($this->db));
        }
    }

    public function CommitTransaction()
    {
        $ret = mysqli_query($this->db, "COMMIT;");
        if(!$ret)
        {
            throw new DatabaseException(mysqli_error($this->db));
        }
    }
}