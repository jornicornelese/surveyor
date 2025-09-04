<?php

namespace Laravel\StaticAnalyzer\NodeResolvers;

use Laravel\StaticAnalyzer\Types\Type;
use PhpParser\Node;

class InterpolatedStringPart extends AbstractResolver
{
    public function resolve(Node\InterpolatedStringPart $node)
    {
        return Type::string();
    }
}
