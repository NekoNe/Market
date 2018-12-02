<?php

interface CustomersStorage
{
    public function Create(User $user);
    public function Read(string $id): ?User;
    public function Update(string $id, callable $updater): ?User;
    public function Delete(string $id);
    public function List(int $offset, int $length): UsersList;

    public function BeginTransaction();
    public function CommitTransaction();
}

