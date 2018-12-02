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
            echo "error: cannot connect to db";
            die; // todo
        }
        // todo: set value type related to balance type in user storage
        $query =<<<EOF
        CREATE TABLE IF NOT EXISTS {$this->tableName} (
          {$this->idField} bigserial primary key,
          {$this->customerIdField} integer,
          {$this->valueField} numeric(16,0)
        );
EOF;
        $ret = pg_query($this->db, $query);
        if(!$ret)
        {
            echo pg_last_error($this->db);
            // todo
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
            echo pg_last_error($this->db);
            die;
        }
        $query =<<<EOF
            SELECT currval(pg_get_serial_sequence('{$this->tableName}', '{$this->idField}'));
EOF;
        $ret = pg_query($this->db, $query);
        if(!$ret)
        {
            echo pg_last_error($this->db);
            die;
        }
        $ret = pg_fetch_result($ret, 0);
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
            echo pg_last_error($this->db);
            die; // todo
        }
        $numRows = pg_numrows($ret);
        if($numRows != 1)
        {
            return null;
        }
        $obj = pg_fetch_object($ret, 0);
        if(!$obj)
        {
            echo pg_last_error($this->db) ;
            die; // todo
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
            echo pg_last_error($this->db);
            die; // todo
        }
        $deleted = pg_affected_rows($ret);
        if($deleted == 0)
        {
            echo 'not found'; // todo
            die;
        }
        if($deleted > 1)
        {
            echo 'internal server error';
            die; // todo
        }
    }

    public function List(int $offset, int $length): TasksList
    {
        $query =<<<EOF
        SELECT * FROM {$this->tableName};
EOF;
        $ret = pg_query($this->db, $query);
        if(!$ret)
        {
            echo pg_last_error();
            die; // todo
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
        $ret = pg_query($this->db, "BEGIN;");
        if(!$ret)
        {
            echo pg_last_error($this->db);
            die; // todo
        }
    }

    public function CommitTransaction()
    {
        $ret = pg_query($this->db, "END;");
        if(!$ret)
        {
            echo pg_last_error($this->db);
            die; // todo
        }
    }
}