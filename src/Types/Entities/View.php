<?php

namespace Laravel\Surveyor\Types\Entities;

use Laravel\Surveyor\Types\ClassType;
use Laravel\Surveyor\Types\Contracts\Type as TypeContract;

class View extends ClassType implements TypeContract
{
    public function __construct(
        public readonly string $view,
        public readonly TypeContract $data,
    ) {
        parent::__construct('Illuminate\View\View');
    }

    public function id(): string
    {
        return $this->view.'::'.$this->data->id();
    }
}
