<?php

namespace Laravel\StaticAnalyzer\Types\Entities;

use Laravel\StaticAnalyzer\Types\ClassType;
use Laravel\StaticAnalyzer\Types\Contracts\Type as TypeContract;

class View extends ClassType implements TypeContract
{
    public readonly string $name;

    public readonly array $data;

    public static function from(ClassType $classType, string $name, array $data): self
    {
        $instance = new self($classType->value);

        $instance->setName($name);
        $instance->setData($data);

        return $instance;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function id(): string
    {
        return $this->value.'::'.json_encode($this->data);
    }
}
