<?php

namespace Laravel\Surveyor\Types\Entities;

use Laravel\Surveyor\Types\ClassType;
use Laravel\Surveyor\Types\Contracts\Type as TypeContract;

class ResourceResponse extends ClassType implements TypeContract
{
    public function __construct(
        public readonly string $resourceClass,
        public readonly TypeContract $wrappedData,
        public readonly bool $isCollection,
        public readonly TypeContract $model,
    ) {
        parent::__construct($isCollection
            ? 'Illuminate\Http\Resources\Json\AnonymousResourceCollection'
            : $resourceClass
        );
    }

    public function id(): string
    {
        return $this->resourceClass.'::'.($this->isCollection ? 'collection' : 'single').'::'.$this->wrappedData->id();
    }

    public function isMoreSpecificThan(TypeContract $type): bool
    {
        if (! $type instanceof ClassType) {
            return false;
        }

        return $this->resolved() === $type->resolved();
    }
}
