<?php

namespace Laravel\StaticAnalyzer\Types;

class VoidType extends AbstractType implements Contracts\Type
{
    public function id(): string
    {
        return 'void';
    }
}
