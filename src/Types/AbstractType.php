<?php

namespace Laravel\StaticAnalyzer\Types;

abstract class AbstractType
{
    public bool $nullable = false;

    public bool $required = true;

    abstract public function id(): string;

    public function nullable(bool $nullable = true): static
    {
        $this->nullable = $nullable;

        return $this;
    }

    public function isMoreSpecificThan(Contracts\Type $type): bool
    {
        return false;
    }

    public function required(bool $required = true): static
    {
        $this->required = $required;

        return $this;
    }

    public function optional(bool $optional = true): static
    {
        $this->required = ! $optional;

        return $this;
    }

    public function isOptional(): bool
    {
        return ! $this->required;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function toString()
    {
        return static::class.':'.$this->id();
    }
}
