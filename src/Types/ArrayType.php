<?php

namespace Laravel\StaticAnalyzer\Types;

class ArrayType extends AbstractType implements Contracts\Type
{
    public function __construct(public readonly array $value)
    {
        //
    }

    /**
     * @return Collection<int|string, mixed>
     */
    public function keys()
    {
        return collect($this->value)->keys();
    }

    public function isMoreSpecificThan(Contracts\Type $type): bool
    {
        return $type instanceof ArrayShapeType && $this->value !== [];
    }

    public function isList(): bool
    {
        return array_is_list($this->value);
    }

    public function id(): string
    {
        return collect($this->value)->toJson();
    }
}
