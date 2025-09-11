<?php

namespace Laravel\Surveyor\NodeResolvers\Stmt;

use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use PhpParser\Node;

class Else_ extends AbstractResolver
{
    public function resolve(Node\Stmt\Else_ $node)
    {
        $this->scope->variables()->startSnapshot($node);
        // $changed = $this->scope->variables()->endSnapshot($node->getStartLine());
    }
}
