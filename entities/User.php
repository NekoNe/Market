<?php

abstract class User implements JsonSerializable
{
    public $id;
    public $balance; // todo: this field should not be visible for api and visible for storage

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
