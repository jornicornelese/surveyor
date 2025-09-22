<?php

namespace Laravel\Surveyor\DocBlockResolvers\ConstExpr;

use Laravel\Surveyor\DocBlockResolvers\AbstractResolver;
use Laravel\Surveyor\Types\Type;
use PHPStan\PhpDocParser\Ast;

class ConstExprTrueNode extends AbstractResolver
{
    public function resolve(Ast\ConstExpr\ConstExprTrueNode $node)
    {
        return Type::bool(true);
    }
}
