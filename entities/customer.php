<?php

class Customer implements JsonSerializable
{
    public $balance;

    public $id; // todo: this field should not be visible for api and visible for storage

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}

class CustomerList implements JsonSerializable
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

    public function addCustomer(Customer $customer)
    {
        array_push($this->customers, $customer);
    }

}