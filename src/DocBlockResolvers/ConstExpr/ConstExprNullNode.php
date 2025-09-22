<?php

namespace Laravel\Surveyor\DocBlockResolvers\ConstExpr;

use Laravel\Surveyor\DocBlockResolvers\AbstractResolver;
use Laravel\Surveyor\Types\Type;
use PHPStan\PhpDocParser\Ast;

class ConstExprNullNode extends AbstractResolver
{
    public function resolve(Ast\ConstExpr\ConstExprNullNode $node)
    {
        return Type::null();
    }
}
