<?php

namespace Laravel\Surveyor\NodeResolvers\Shared;

use Illuminate\Database\Eloquent\Model;
use Laravel\Surveyor\Types\ClassType;
use PhpParser\Node;

trait ResolvesModelFromExpression
{
    protected function resolveModelFromExpression(Node\Expr $expr): ?ClassType
    {
        while ($expr instanceof Node\Expr\MethodCall || $expr instanceof Node\Expr\NullsafeMethodCall) {
            $expr = $expr->var;
        }

        if ($expr instanceof Node\Expr\StaticCall && $expr->class instanceof Node\Name) {
            $className = $this->scope->getUse($expr->class->toString());
            $classType = new ClassType($className);

            if (class_exists($classType->resolved())
                && is_subclass_of($classType->resolved(), Model::class)) {
                return $classType;
            }
        }

        return null;
    }
}
