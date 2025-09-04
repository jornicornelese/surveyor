<?php

namespace Laravel\StaticAnalyzer\NodeResolvers\Expr\BinaryOp;

use Laravel\StaticAnalyzer\NodeResolvers\AbstractResolver;
use Laravel\StaticAnalyzer\Types\Type;
use PhpParser\Node;

class BooleanOr extends AbstractResolver
{
    public function resolve(Node\Expr\BinaryOp\BooleanOr $node)
    {
        return Type::bool();
    }
}
