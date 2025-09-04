<?php

namespace Laravel\StaticAnalyzer\Result;

use Laravel\StaticAnalyzer\Types\ClassType;

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
        $this->variables()->add('this', new ClassType($className), 0);
    }
}
