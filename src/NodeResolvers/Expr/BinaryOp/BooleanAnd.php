<?php

namespace Laravel\Surveyor\NodeResolvers\Expr\BinaryOp;

use Laravel\Surveyor\Analysis\Condition;
use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use Laravel\Surveyor\Types\Type;
use PhpParser\Node;

class BooleanAnd extends AbstractResolver
{
    public function resolve(Node\Expr\BinaryOp\BooleanAnd $node)
    {
        return Type::bool();
    }

    public function resolveForCondition(Node\Expr\BinaryOp\BooleanAnd $node)
    {
        $left = $this->from($node->left);
        $right = $this->from($node->right);

        if ($left instanceof Condition) {
            if ($this->scope->state()->canHandle($left->node)) {
                $this->scope->state()->narrow($left->node, $left->apply(), $node);
            }
        }

        if ($right instanceof Condition) {
            if ($this->scope->state()->canHandle($right->node)) {
                $this->scope->state()->narrow($right->node, $right->apply(), $node);
            }
        }
    }
}
