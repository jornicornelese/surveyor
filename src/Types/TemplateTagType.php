<?php

namespace Laravel\Surveyor\Types;

use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;

class TemplateTagType extends AbstractType
{
    public function __construct(public readonly TemplateTagValueNode $value)
    {
        //
    }

    public function id(): string
    {
        return $this->value->name;
    }
}
