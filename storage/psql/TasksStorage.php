<?php

include_once __DIR__ ."/../TasksStorage.php";


class TasksPsqlStorage implements TasksStorage
{
    private $db;

    private $tableName;
    private $idField = "id";
    private $valueField = "value";
    private $customerIdField = "customer";

    public function __construct(PsqlConfig $config, string $tableName)
    {
        $this->tableName = $tableName;

        $this->db = pg_connect("$config->host $config->port $config->dbname $config->credentials");
        if(!$this->db)
        {
            throw new DatabaseException(pg_last_error($this->db));
        }
        $query =<<<EOF
        CREATE TABLE IF NOT EXISTS {$this->tableName} (
          {$this->idField} bigserial primary key,
          {$this->customerIdField} integer,
          {$this->valueField} bigint 
        );
EOF;
        $ret = pg_query($this->db, $query);
        if(!$ret)
        {
            throw new DatabaseException(pg_last_error($this->db));
        }
    }

    public function __destruct()
    {
        pg_close($this->db);
    }

    public function Create(Task $task)
    {
        $query =<<<EOF
        INSERT INTO {$this->tableName} ({$this->customerIdField},{$this->valueField})
        VALUES ({$task->customerId}, {$task->value});
EOF;
        $ret = pg_query($this->db, $query);
        if(!$ret)
        {
            throw new DatabaseException(pg_last_error($this->db));
        }
        $query =<<<EOF
            SELECT currval(pg_get_serial_sequence('{$this->tableName}', '{$this->idField}'));
EOF;
        $ret = pg_query($this->db, $query);
        if(!$ret)
        {
            throw new DatabaseException(pg_last_error($this->db));
        }
        $ret = pg_fetch_result($ret, 0);
        if(!$ret)
        {
            throw new DatabaseException(pg_last_error($this->db));
        }
        $task->id = intval($ret);
    }

    public function Read(string $tid): ?Task
    {
        $query =<<<EOF
        SELECT * FROM {$this->tableName} WHERE {$this->idField} = {$tid};
EOF;
        $ret = pg_query($this->db, $query);
        if(!$ret)
        {
            throw new DatabaseException(pg_last_error($this->db));
        }
        $numRows = pg_numrows($ret);
        if($numRows != 1)
        {   // todo: handle numRows > 1 and numRows == 0
            throw new ObjectNotFoundException("Task:{$tid}");
        }
        $obj = pg_fetch_object($ret, 0);
        if(!$obj)
        {
            throw new DatabaseException(pg_last_error($this->db));
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
        $ret = pg_query($this->db, $query);
        if(!$ret)
        {
            throw new DatabaseException(pg_last_error($this->db));
        }
        $deleted = pg_affected_rows($ret);
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
        SELECT * FROM {$this->tableName} LIMIT {$length} OFFSET {$offset};
EOF;
        $ret = pg_query($this->db, $query);
        if(!$ret)
        {
            throw new DatabaseException(pg_last_error($this->db));
        }
        $list = new TasksList();
        while($obj = pg_fetch_object($ret))
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
        $ret = pg_query($this->db, "BEGIN TRANSACTION;");
        if(!$ret)
        {
            throw new DatabaseException(pg_last_error($this->db));
        }
    }

    public function CommitTransaction()
    {
        $ret = pg_query($this->db, "COMMIT;");
        if(!$ret)
        {
            throw new DatabaseException(pg_last_error($this->db));
        }
    }
}