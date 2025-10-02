<?php

namespace Laravel\Surveyor\NodeResolvers\Expr;

use Laravel\Surveyor\Analysis\Condition;
use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use Laravel\Surveyor\NodeResolvers\Shared\ResolvesPropertyFetches;
use Laravel\Surveyor\Types\Type;
use PhpParser\Node;

class PropertyFetch extends AbstractResolver
{
    use ResolvesPropertyFetches;

    public function resolve(Node\Expr\PropertyFetch $node)
    {
        return $this->resolvePropertyFetch($node) ?? Type::mixed();
    }

    public function resolveForCondition(Node\Expr\PropertyFetch $node)
    {
        $type = $this->fromOutsideOfCondition($node);

        return new Condition($node, $type);
    }
}
