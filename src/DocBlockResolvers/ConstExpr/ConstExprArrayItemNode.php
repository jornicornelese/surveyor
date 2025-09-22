<?php

namespace Laravel\Surveyor\DocBlockResolvers\ConstExpr;

use Laravel\Surveyor\DocBlockResolvers\AbstractResolver;
use Laravel\Surveyor\Types\Type;
use PHPStan\PhpDocParser\Ast;

class ConstExprArrayItemNode extends AbstractResolver
{
    public function resolve(Ast\ConstExpr\ConstExprArrayItemNode $node)
    {
        return Type::arrayShape(Type::from($node->key), Type::from($node->value));
    }
}
