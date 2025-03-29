<?php

namespace App\Common\Event;

abstract class Base
{

    public function __get($name)
    {
        if (property_exists($this, $name))
            return $this->$name;
    }
}
