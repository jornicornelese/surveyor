<?php

namespace Laravel\Surveyor\Types;

class FloatType extends NumberType
{
    public function __construct(public readonly ?float $value = null)
    {
        //
    }

    public function id(): string
    {
        return $this->value === null ? 'float' : (string) $this->value;
    }
}
