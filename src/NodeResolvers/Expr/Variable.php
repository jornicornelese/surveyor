<?php

namespace Laravel\Surveyor\NodeResolvers\Expr;

use Laravel\Surveyor\Analysis\Condition;
use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use Laravel\Surveyor\Types\Type;
use PhpParser\Node;

class Variable extends AbstractResolver
{
    public function resolve(Node\Expr\Variable $node)
    {
        return $this->scope->variables()->getAtLine($node->name, $node)['type'] ?? Type::mixed();
    }

    public function resolveForCondition(Node\Expr\Variable $node)
    {
        $type = $this->resolve($node);

        return new Condition($node->name, $type, $node);
    }
}
