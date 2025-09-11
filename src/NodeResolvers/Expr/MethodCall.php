<?php

namespace Laravel\Surveyor\NodeResolvers\Expr;

use Laravel\Surveyor\Debug\Debug;
use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use Laravel\Surveyor\Types\ClassType;
use Laravel\Surveyor\Types\Type;
use PhpParser\Node;

class MethodCall extends AbstractResolver
{
    public function resolve(Node\Expr\MethodCall $node)
    {
        $var = $this->from($node->var);

        if (! $var instanceof ClassType) {
            Debug::ddIfInterested($this->scope->variables());
            Debug::ddFromClass($var, $node, 'non-class for method call?');
        }

        return Type::union(
            ...$this->reflector->methodReturnType($this->scope->getUse($var->value), $node->name, $node)
        );
    }
}
