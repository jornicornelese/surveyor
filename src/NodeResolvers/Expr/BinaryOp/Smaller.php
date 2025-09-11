<?php

namespace Laravel\Surveyor\NodeResolvers\Expr\BinaryOp;

use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use Laravel\Surveyor\Types\Type;
use PhpParser\Node;

class Smaller extends AbstractResolver
{
    public function resolve(Node\Expr\BinaryOp\Smaller $node)
    {
        return Type::bool();
    }

    public function resolveForCondition(Node\Expr\BinaryOp\Smaller $node)
    {
        return null;
    }
}
