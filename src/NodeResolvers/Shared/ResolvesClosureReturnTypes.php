<?php

namespace Laravel\Surveyor\NodeResolvers\Shared;

use Laravel\Surveyor\Types\CallableType;
use Laravel\Surveyor\Types\Contracts\Type as TypeContract;
use PhpParser\Node;

trait ResolvesClosureReturnTypes
{
    protected function resolveClosureReturnType(Node\Expr $expr): ?TypeContract
    {
        $returnTypeNode = match (true) {
            $expr instanceof Node\Expr\ArrowFunction => $expr->returnType,
            $expr instanceof Node\Expr\Closure => $expr->returnType,
            default => null,
        };

        if ($returnTypeNode) {
            return $this->from($returnTypeNode);
        }

        $resolved = $this->from($expr);

        if ($resolved instanceof CallableType) {
            return $resolved->returnType;
        }

        return $resolved;
    }
}
