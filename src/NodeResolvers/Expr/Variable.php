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
        if (! $node->name instanceof Node\Expr\Variable) {
            return $this->scope->state()->getAtLine($node)?->type() ?? Type::mixed();
        }

        // The ol' double dollar ($$key)
        return Type::mixed();
    }

    public function resolveForCondition(Node\Expr\Variable $node)
    {
        return Condition::from($node, $this->resolve($node));
    }
}
