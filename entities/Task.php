<?php

class Task implements JsonSerializable
{
    public $id;
    public $value;
    public $customerId;

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}

class TasksList implements JsonSerializable
{
    // todo: add list len and offset
    private $tasks;

    public function __construct()
    {
        $this->tasks = array();
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    public function addTask(Task $task)
    {
        array_push($this->tasks, $task);
    }
}