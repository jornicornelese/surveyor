<?php

namespace Laravel\Surveyor\NodeResolvers\Stmt;

use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use Laravel\Surveyor\Result\VariableState;
use Laravel\Surveyor\Types\Type;
use PhpParser\Node;

class Return_ extends AbstractResolver
{
    public function resolve(Node\Stmt\Return_ $node)
    {
        $this->scope->state()->markSnapShotAsTerminated($node);

        $type = match (true) {
            $node->expr !== null => $this->from($node->expr),
            default => Type::void(),
        };

        if ($type instanceof VariableState) {
            $type = $type->type();
        }

        $this->scope->addReturnType(Type::collapse($type ?? Type::mixed()), $node->getStartLine());

        return null;
    }
}
