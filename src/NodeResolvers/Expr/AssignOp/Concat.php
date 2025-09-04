<?php

namespace Laravel\StaticAnalyzer\NodeResolvers\Expr\AssignOp;

use Laravel\StaticAnalyzer\NodeResolvers\AbstractResolver;
use Laravel\StaticAnalyzer\Types\Type;
use PhpParser\Node;

class Concat extends AbstractResolver
{
    public function resolve(Node\Expr\AssignOp\Concat $node)
    {
        return Type::string();
    }
}
