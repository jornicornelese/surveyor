<?php

namespace Laravel\Surveyor\NodeResolvers\Expr\BinaryOp;

use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use Laravel\Surveyor\Types\StringType;
use Laravel\Surveyor\Types\Type;
use PhpParser\Node;

class Concat extends AbstractResolver
{
    public function resolve(Node\Expr\BinaryOp\Concat $node)
    {
        $left = $this->from($node->left);
        $right = $this->from($node->right);

        if (Type::is($left, StringType::class) && Type::is($right, StringType::class)) {
            return Type::string($left->value.$right->value);
        }

        return Type::string();
    }
}
