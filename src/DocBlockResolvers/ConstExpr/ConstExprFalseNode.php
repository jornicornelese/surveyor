<?php

namespace Laravel\Surveyor\DocBlockResolvers\ConstExpr;

use Laravel\Surveyor\DocBlockResolvers\AbstractResolver;
use Laravel\Surveyor\Types\Type;
use PHPStan\PhpDocParser\Ast;

class ConstExprFalseNode extends AbstractResolver
{
    public function resolve(Ast\ConstExpr\ConstExprFalseNode $node)
    {
        return Type::bool(false);
    }
}
