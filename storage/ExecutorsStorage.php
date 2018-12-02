<?php

interface ExecutorsStorage
{
    public function Create(Executor $executor);
    public function Read(string $id): ?Executor;
    public function Update(string $id, callable $updater);
    public function Delete(string $id);
    public function List(int $page, int $pageSize): ExecutorList;
}