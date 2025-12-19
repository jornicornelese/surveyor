<?php

namespace Laravel\Surveyor\NodeResolvers\Stmt;

use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use PhpParser\Node;

class EnumCase extends AbstractResolver
{
    public function resolve(Node\Stmt\EnumCase $node)
    {
        $this->scope->addCase($node->name, $this->from($node->expr));

        return null;
    }
}
