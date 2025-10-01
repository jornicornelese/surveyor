<?php

namespace Laravel\Surveyor\DocBlockResolvers\Type;

use Laravel\Surveyor\DocBlockResolvers\AbstractResolver;
use Laravel\Surveyor\Types\Type;
use PHPStan\PhpDocParser\Ast;

class CallableTypeParameterNode extends AbstractResolver
{
    public function resolve(Ast\Type\CallableTypeParameterNode $node)
    {
        $templateTag = $this->scope->getTemplateTag($node->type->name);

        if ($templateTag) {
            return $templateTag;
        }

        return Type::callable([], $this->from($node->type));
    }
}
