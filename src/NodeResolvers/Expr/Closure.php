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
        foreach ($node->stmts as $stmt) {
            $this->from($stmt);
        }

        $returnTypes = $this->scope->returnTypes();

        if ($node->returnType) {
            $returnTypes[] = $this->from($node->returnType);
        }

        return Type::callable([], Type::union(...array_column($returnTypes, 'type')));
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
