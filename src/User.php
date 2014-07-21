<?php

use Trucker\Resource\Model;

class User extends Model
{
    public function __toString()
    {
        return $this->getId() . ' - ' . $this->login;
    }
}