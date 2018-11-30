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

class TaskList implements JsonSerializable
{
    private $customers;

    public function __construct()
    {
        $this->customers = array();
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    public function addCustomer(Task $customer)
    {
        array_push($this->customers, $customer);
    }

}