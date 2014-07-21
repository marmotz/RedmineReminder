<?php

use Trucker\Resource\Model;

class Issue extends Model
{
    public function __toString()
    {
        return $this->getId() . ' - ' . $this->description;
    }
}