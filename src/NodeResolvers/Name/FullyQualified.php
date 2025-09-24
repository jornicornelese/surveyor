<?php

namespace Laravel\Surveyor\NodeResolvers\Name;

use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use Laravel\Surveyor\Types\Type;
use PhpParser\Node;

class FullyQualified extends AbstractResolver
{
    public function resolve(Node\Name\FullyQualified $node)
    {
        return Type::from($node->toString());
    }

    public function resolveForCondition(Node\Name\FullyQualified $node)
    {
        return Type::from($node->toString());
    }
}
