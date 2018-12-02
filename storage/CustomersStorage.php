<?php

interface CustomersStorage
{
    public function Create(Customer $customer);
    public function Read(string $id): ?Customer;
    public function Update(string $id, callable $updater): ?Customer;
    public function Delete(string $id);
    public function List(int $offset, int $length): CustomerList;
}

