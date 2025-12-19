<?php

namespace Laravel\Surveyor\DocBlockResolvers\PhpDoc;

use Laravel\Surveyor\DocBlockResolvers\AbstractResolver;
use PHPStan\PhpDocParser\Ast;

class MixinTagValueNode extends AbstractResolver
{
    public function resolve(Ast\PhpDoc\MixinTagValueNode $node)
    {
        //
    }
}
