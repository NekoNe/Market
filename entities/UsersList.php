<?php

class UsersList implements JsonSerializable
{
    // todo: select name according to object type it stores
    // todo: or just made this class an abstract one
    private $users;

    public function __construct()
    {
        $this->users = array();
    }
    public function addUser(User $user)
    {
        array_push($this->users, $user);
    }
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}