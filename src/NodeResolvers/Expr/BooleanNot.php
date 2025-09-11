<?php

namespace Laravel\Surveyor\NodeResolvers\Expr;

use Laravel\Surveyor\Analysis\Condition;
use Laravel\Surveyor\Debug\Debug;
use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use Laravel\Surveyor\Types\Type;
use PhpParser\Node;

class BooleanNot extends AbstractResolver
{
    public function resolve(Node\Expr\BooleanNot $node)
    {
        return Type::bool();
    }

    public function resolveForCondition(Node\Expr\BooleanNot $node)
    {
        $type = $this->from($node->expr);

        if (! $type instanceof Condition) {
            Debug::ddFromClass($type, $node, 'boolean not is not a condition');
        }

        return $type->toggle();
    }
}
