<?php

namespace Laravel\StaticAnalyzer\Types;

class ArrayShapeType extends AbstractType implements Contracts\Type
{
    public function __construct(
        public readonly Contracts\Type $keyType,
        public readonly Contracts\Type $valueType,
    ) {
        //
    }

    public function id(): string
    {
        return $this->keyType->id().':'.$this->valueType->id();
    }

    public function isMoreSpecificThan(Contracts\Type $type): bool
    {
        if (! $type instanceof ArrayType) {
            return false;
        }

        return count($type->value) === 0;
    }
}
