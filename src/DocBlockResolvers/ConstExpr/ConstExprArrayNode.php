<?php

namespace Laravel\Surveyor\DocBlockResolvers\ConstExpr;

use Laravel\Surveyor\DocBlockResolvers\AbstractResolver;
use Laravel\Surveyor\Types\Type;
use PHPStan\PhpDocParser\Ast;

class ConstExprArrayNode extends AbstractResolver
{
    public function resolve(Ast\ConstExpr\ConstExprArrayNode $node)
    {
        return Type::array(
            collect($node->items)
                ->map(fn ($item) => $this->from($item))
                ->all(),
        );
    }
}
