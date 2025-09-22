<?php

namespace Laravel\Surveyor\DocBlockResolvers\ConstExpr;

use Laravel\Surveyor\DocBlockResolvers\AbstractResolver;
use Laravel\Surveyor\Types\Type;
use PHPStan\PhpDocParser\Ast;

class ConstExprIntegerNode extends AbstractResolver
{
    public function resolve(Ast\ConstExpr\ConstExprIntegerNode $node)
    {
        return Type::int($node->value);
    }
}
