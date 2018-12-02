<?php

interface TasksStorage
{
    public function Read(string $tid): ?Task;
    public function List(int $offset, int $length): TaskList;
}