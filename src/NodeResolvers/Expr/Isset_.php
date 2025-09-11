<?php

namespace Laravel\Surveyor\NodeResolvers\Expr;

use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use Laravel\Surveyor\Types\Type;
use PhpParser\Node;

class Isset_ extends AbstractResolver
{
    public function resolve(Node\Expr\Isset_ $node)
    {
        return Type::bool();
    }

    public function resolveForCondition(Node\Expr\Isset_ $node)
    {
        foreach ($node->vars as $var) {
            if ($var instanceof Node\Expr\Variable) {
                $this->scope->variables()->removeType($var->name, $node, Type::null());
            } elseif ($var instanceof Node\Expr\PropertyFetch) {
                $this->scope->properties()->removeType($var->name->name, $node, Type::null());
            } elseif ($var instanceof Node\Expr\ArrayDimFetch) {
                if ($var->var instanceof Node\Expr\Variable) {
                    $this->scope->variables()->removeArrayKeyType($var->var->name, $var->dim->value, Type::null(), $node);
                } elseif ($var->var instanceof Node\Expr\PropertyFetch) {
                    $this->scope->properties()->removeArrayKeyType($var->var->name->name, $var->dim->value, Type::null(), $node);
                }
            }
        }
    }
}
