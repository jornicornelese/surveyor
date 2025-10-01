<?php

namespace Laravel\Surveyor\NodeResolvers\Expr\BinaryOp;

use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use Laravel\Surveyor\Types\Type;
use PhpParser\Node;

class GreaterOrEqual extends AbstractResolver
{
    public function resolve(Node\Expr\BinaryOp\GreaterOrEqual $node)
    {
        return Type::bool();
    }

    public function resolveForCondition(Node\Expr\BinaryOp\GreaterOrEqual $node)
    {
        return null;
    }
}
