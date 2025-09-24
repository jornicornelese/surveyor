<?php

namespace Laravel\Surveyor\NodeResolvers;

use PhpParser\Node;

class Arg extends AbstractResolver
{
    public function resolve(Node\Arg $node)
    {
        return null;
    }

    public function resolveForCondition(Node\Arg $node)
    {
        return $this->fromOutsideOfCondition($node);
    }
}
