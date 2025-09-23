<?php

namespace Laravel\Surveyor\NodeResolvers;

use PhpParser\Node;

class MatchArm extends AbstractResolver
{
    public function resolve(Node\MatchArm $node)
    {
        return null;
    }
}
