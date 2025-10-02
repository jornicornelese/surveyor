<?php

namespace Laravel\Surveyor\NodeResolvers\Stmt;

use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use Laravel\Surveyor\Types\ArrayShapeType;
use Laravel\Surveyor\Types\ArrayType;
use Laravel\Surveyor\Types\Type;
use PhpParser\Node;

class Foreach_ extends AbstractResolver
{
    public function resolve(Node\Stmt\Foreach_ $node)
    {
        $iterating = $this->from($node->expr);

        if ($node->keyVar) {
            $this->scope->state()->add(
                $node->keyVar,
                match (true) {
                    $iterating instanceof ArrayType => $iterating->keyType(),
                    $iterating instanceof ArrayShapeType => $iterating->keyType,
                    default => Type::mixed(),
                },
            );
        }

        if ($node->valueVar instanceof Node\Expr\List_) {
            foreach ($node->valueVar->items as $item) {
                $this->scope->state()->add($item->value, $this->from($item->value));
            }
        } else {
            $this->scope->state()->add(
                $node->valueVar,
                match (true) {
                    $iterating instanceof ArrayType => $iterating->valueType(),
                    $iterating instanceof ArrayShapeType => $iterating->valueType,
                    default => Type::mixed(),
                },
            );
        }

        return null;
    }
}
