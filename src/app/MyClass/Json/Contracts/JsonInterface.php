<?php

namespace IntercaseTools\MyClass\Json\Contracts;

interface JsonInterface
{
    public function set($stringJson);
    public function validRequiredFields($array);
}