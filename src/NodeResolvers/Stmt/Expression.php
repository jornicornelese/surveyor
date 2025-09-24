<?php

namespace Laravel\Surveyor\NodeResolvers\Stmt;

use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use PhpParser\Node;

class Expression extends AbstractResolver
{
    public function resolve(Node\Stmt\Expression $node)
    {
        // I think this is a bug in the parser, but doing this for now
        if ($node->expr instanceof Node\Expr\Assign) {
            if ($comment = $node->getDocComment()) {
                $this->scope->setPendingDocBlock($comment);
            }
        }

        return null;
    }

    public function resolveForCondition(Node\Stmt\Expression $node)
    {
        return null;
    }
}
