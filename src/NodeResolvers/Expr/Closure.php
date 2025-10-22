<?php

namespace Laravel\Surveyor\NodeResolvers\Expr;

use Laravel\Surveyor\Analysis\Scope;
use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use Laravel\Surveyor\Types\Type;
use PhpParser\Node;

class Closure extends AbstractResolver
{
    public function resolve(Node\Expr\Closure $node)
    {
        return Type::callable([], $node->returnType ? $this->from($node->returnType) : null);
    }

    public function scope(): Scope
    {
        return $this->scope->newChildScope();
    }

    public function exitScope(): Scope
    {
        return $this->scope->parent();
    }
}
