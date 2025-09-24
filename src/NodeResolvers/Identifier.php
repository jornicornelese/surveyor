<?php

namespace Laravel\Surveyor\NodeResolvers;

use Laravel\Surveyor\Types\Type;
use PhpParser\Node;

class Identifier extends AbstractResolver
{
    public function resolve(Node\Identifier $node)
    {
        return Type::from($node->name);
    }

    public function resolveForCondition(Node\Identifier $node)
    {
        return Type::from($node->name);
    }
}
