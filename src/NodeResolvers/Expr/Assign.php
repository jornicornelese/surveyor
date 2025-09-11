<?php

namespace Laravel\Surveyor\NodeResolvers\Expr;

use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use Laravel\Surveyor\Types\IntType;
use Laravel\Surveyor\Types\StringType;
use Laravel\Surveyor\Types\Type;
use PhpParser\Node;

class Assign extends AbstractResolver
{
    public function resolve(Node\Expr\Assign $node)
    {
        switch (true) {
            case $node->var instanceof Node\Expr\Variable:
                $this->scope->variables()->add(
                    $node->var->name,
                    $this->from($node->expr),
                    $node
                );
                break;

            case $node->var instanceof Node\Expr\PropertyFetch:
                $this->scope->properties()->add(
                    $node->var->name->name,
                    $this->from($node->expr),
                    $node
                );
                break;

            case $node->var instanceof Node\Expr\ArrayDimFetch:
                $dim = $node->var->dim === null ? Type::int() : $this->from($node->var->dim);
                $validDim = Type::is($dim, StringType::class, IntType::class) && $dim->value !== null;

                if (! $validDim) {
                    break;
                }

                if ($node->var->var instanceof Node\Expr\Variable) {
                    $this->scope->variables()->updateArrayKey(
                        $node->var->var->name,
                        $dim->value,
                        $this->from($node->expr),
                        $node,
                    );

                    break;
                }

                if ($node->var->var instanceof Node\Expr\PropertyFetch) {
                    $this->scope->properties()->updateArrayKey(
                        $node->var->var->name,
                        $dim->value,
                        $this->from($node->expr),
                        $node,
                    );

                    break;
                }

                dd('assign: array dim fetch but not a variable or property fetch??', $node, $dim);

                break;
        }

        return null;
    }
}
