<?php

namespace Laravel\Surveyor\Result;

use Laravel\Surveyor\Types\ClassType;
use PhpParser\NodeAbstract;

class StateTracker
{
    protected StateTrackerItem $variableTracker;

    protected StateTrackerItem $propertyTracker;

    public function __construct()
    {
        $this->variableTracker = new StateTrackerItem;
        $this->propertyTracker = new StateTrackerItem;
    }

    public function variables()
    {
        return $this->variableTracker;
    }

    public function properties()
    {
        return $this->propertyTracker;
    }

    public function setThis(string $className): void
    {
        $this->variables()->add('this', new ClassType($className), new class extends NodeAbstract
        {
            public function getType(): string
            {
                return 'NodeAbstract';
            }

            public function getSubNodeNames(): array
            {
                return [];
            }
        });
    }
}
