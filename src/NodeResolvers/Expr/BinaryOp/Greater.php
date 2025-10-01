<?php

namespace Laravel\Surveyor\NodeResolvers\Expr\BinaryOp;

use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use Laravel\Surveyor\Types\Type;
use PhpParser\Node;

class Greater extends AbstractResolver
{
    public function resolve(Node\Expr\BinaryOp\Greater $node)
    {
        return Type::bool();
    }

    public function resolveForCondition(Node\Expr\BinaryOp\Greater $node)
    {
        return null;
    }
}
