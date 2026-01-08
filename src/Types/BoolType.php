<?php

namespace Laravel\Surveyor\Types;

class BoolType extends AbstractType implements Contracts\Type
{
    public function __construct(public readonly ?bool $value = null)
    {
        //
    }

    public function id(): string
    {
        if ($this->value === null) {
            return 'bool';
        }

        return $this->value ? 'true' : 'false';
    }
}
