<?php

namespace Laravel\Surveyor\DocBlockResolvers\ConstExpr;

use Laravel\Surveyor\DocBlockResolvers\AbstractResolver;
use Laravel\Surveyor\Types\Type;
use PHPStan\PhpDocParser\Ast;

class ConstExprStringNode extends AbstractResolver
{
    public function resolve(Ast\ConstExpr\ConstExprStringNode $node)
    {
        return Type::string($node->value);
    }
}
