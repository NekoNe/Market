<?php

interface TasksStorage
{
    public function Create(Task $task);
    public function Read(string $tid): ?Task;
    public function Delete(string $tid);
    public function List(int $offset, int $length): TasksList;
}