<?php

namespace Laravel\Surveyor\DocBlockResolvers\Type;

use Laravel\Surveyor\DocBlockResolvers\AbstractResolver;
use Laravel\Surveyor\Types\Type;
use PHPStan\PhpDocParser\Ast;

class CallableTypeNode extends AbstractResolver
{
    public function resolve(Ast\Type\CallableTypeNode $node)
    {
        $params = array_map(fn ($param) => $this->from($param), $node->parameters);

        return Type::callable($params, $this->from($node->returnType));
    }
}
