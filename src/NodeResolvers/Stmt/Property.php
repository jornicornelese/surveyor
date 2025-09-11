<?php

namespace Laravel\Surveyor\NodeResolvers\Stmt;

use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use Laravel\Surveyor\Types\Type;
use PhpParser\Node;

class Property extends AbstractResolver
{
    public function resolve(Node\Stmt\Property $node)
    {
        foreach ($node->props as $prop) {
            $this->scope->properties()->add(
                $prop->name->name,
                $node->type ? $this->from($node->type) : Type::null(),
                $node,
            );
        }

        return null;
    }
}
