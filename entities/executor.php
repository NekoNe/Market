<?php

// todo: it looks like Executor and Customer violates DRY principle

class Executor implements JsonSerializable
{
    public $balance;
    private $id;

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

}

class ExecutorList implements JsonSerializable
{
    private $executors;

    public function __construct()
    {
        $this->executors = array();
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    public function addExecutor(Executor $executor)
    {
        array_push($this->executors, $executor);
    }
}