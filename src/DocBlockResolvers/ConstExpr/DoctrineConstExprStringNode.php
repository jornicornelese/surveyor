<?php

namespace Laravel\Surveyor\DocBlockResolvers\ConstExpr;

use Laravel\Surveyor\DocBlockResolvers\AbstractResolver;
use Laravel\Surveyor\Types\Type;
use PHPStan\PhpDocParser\Ast;

class DoctrineConstExprStringNode extends AbstractResolver
{
    public function resolve(Ast\ConstExpr\DoctrineConstExprStringNode $node)
    {
        return Type::string($node->value);
    }
}
