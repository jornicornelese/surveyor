<?php

namespace Laravel\Surveyor\NodeResolvers\Expr;

use Laravel\Surveyor\Analysis\Condition;
use Laravel\Surveyor\Debug\Debug;
use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use Laravel\Surveyor\Result\VariableState;
use Laravel\Surveyor\Types\ArrayShapeType;
use Laravel\Surveyor\Types\ArrayType;
use Laravel\Surveyor\Types\IntType;
use Laravel\Surveyor\Types\StringType;
use Laravel\Surveyor\Types\Type;
use PhpParser\Node;

class Assign extends AbstractResolver
{
    public function resolve(Node\Expr\Assign $node)
    {
        Debug::interested($node->getStartLine() === 44);

        $result = $this->getResult($node);

        if ($this->scope->analyzingConditionPaused()) {
            return $result;
        }

        return null;
    }

    public function resolveForCondition(Node\Expr\Assign $node)
    {
        $result = $this->fromOutsideOfCondition($node);

        if ($result && $result instanceof VariableState) {
            // If it's assigned in the condition, it should not be terminated
            $result->markNonTerminable();
        }

        if ($node->var instanceof Node\Expr\Variable) {
            return new Condition($node->var, $this->from($node->expr));
        }

        Debug::ddAndOpen($node, 'assign: variable but not a variable??');
    }

    protected function getResult(Node\Expr\Assign $node)
    {
        if ($node->var instanceof Node\Expr\ArrayDimFetch) {
            return $this->resolveForDimFetch($node);
        }

        if ($node->var instanceof Node\Expr\List_) {
            $result = [];
            $expr = $this->from($node->expr);

            $values = match (true) {
                $expr instanceof ArrayType => $expr->value,
                $expr instanceof ArrayShapeType => array_fill(0, count($node->var->items), $expr->valueType),
                default => [],
            };

            foreach ($node->var->items as $index => $item) {
                if ($item->value instanceof Node\Expr\ArrayDimFetch) {
                    $dim = $item->value->dim === null ? Type::int() : $this->from($item->value->dim);
                    $validDim = Type::is($dim, StringType::class, IntType::class) && $dim->value !== null;

                    if ($validDim) {
                        $result[] = $this->scope->state()->updateArrayKey(
                            $item->value->var,
                            $dim->value,
                            $values[$index] ?? Type::mixed(),
                            $node,
                        );
                    }
                } else {
                    $result[] = $this->scope->state()->add($item->value, $values[$index] ?? Type::mixed());
                }
            }

            return $result;
        }

        return $this->scope->state()->add($node->var, $this->from($node->expr));
    }

    protected function resolveForDimFetch(Node\Expr\Assign $node)
    {
        /** @var Node\Expr\ArrayDimFetch $dimFetch */
        $dimFetch = $node->var;

        $dim = $dimFetch->dim === null ? Type::int() : $this->from($dimFetch->dim);
        $validDim = Type::is($dim, StringType::class, IntType::class) && $dim->value !== null;

        if ($validDim) {
            $this->scope->state()->updateArrayKey(
                $dimFetch->var,
                $dim->value,
                $this->from($node->expr),
                $node,
            );
        }
    }
}
