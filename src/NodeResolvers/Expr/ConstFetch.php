<?php

namespace Laravel\Surveyor\NodeResolvers\Expr;

use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use Laravel\Surveyor\Types\Type;
use PhpParser\Node;

class ConstFetch extends AbstractResolver
{
    public function resolve(Node\Expr\ConstFetch $node)
    {
        return Type::from($node->name->toString());
    }

    public function resolveForCondition(Node\Expr\ConstFetch $node)
    {
        return Type::from($node->name->toString());
    }
}
