<?php

namespace Laravel\StaticAnalyzer\NodeResolvers\Scalar;

use Laravel\StaticAnalyzer\NodeResolvers\AbstractResolver;
use Laravel\StaticAnalyzer\Types\Type;
use PhpParser\Node;

class InterpolatedString extends AbstractResolver
{
    public function resolve(Node\Scalar\InterpolatedString $node)
    {
        return Type::string();
    }
}
