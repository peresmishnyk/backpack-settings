<?php

namespace Peresmishnyk\BackpackSettings\Exceptions;

class ConfigurationException extends \Exception
{
    protected $message = 'Key not set';   // exception message

    // custom string representation of object
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
