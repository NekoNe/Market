<?php

class Task implements JsonSerializable
{
    public $value;
    private $id;

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}

class TasksList implements JsonSerializable
{
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