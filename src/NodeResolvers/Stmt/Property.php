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
            $types = [];

            if ($node->getDocComment()) {
                $docType = $this->docBlockParser->parseVar($node->getDocComment());

                if ($docType) {
                    $types[] = $docType;
                }
            }

            if ($node->type) {
                $types[] = $this->from($node->type);
            }

            $this->scope->state()->add(
                $prop,
                Type::union(...$types),
            );
        }

        return null;
    }
}
