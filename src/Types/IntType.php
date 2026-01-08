<?php

namespace Laravel\Surveyor\Types;

class IntType extends NumberType
{
    public function __construct(public readonly ?int $value = null)
    {
        //
    }

    public function id(): string
    {
        return $this->value === null ? 'int' : (string) $this->value;
    }
}
