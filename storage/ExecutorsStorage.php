<?php

interface ExecutorsStorage
{
    public function Create(User $executor);
    public function Read(string $id): ?User;
    public function Update(string $id, callable $updater);
    public function Delete(string $id);
    public function List(int $page, int $pageSize): UsersList;

    public function BeginTransaction();
    public function CommitTransaction();
}