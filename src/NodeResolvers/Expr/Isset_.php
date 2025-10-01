<?php

namespace Laravel\Surveyor\NodeResolvers\Expr;

use Laravel\Surveyor\Analysis\Condition;
use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use Laravel\Surveyor\NodeResolvers\Shared\ResolvesArrays;
use Laravel\Surveyor\Types\Contracts\Type as TypeContract;
use Laravel\Surveyor\Types\MixedType;
use Laravel\Surveyor\Types\Type;
use PhpParser\Node;

class Isset_ extends AbstractResolver
{
    use ResolvesArrays;

    public function resolve(Node\Expr\Isset_ $node)
    {
        return Type::bool();
    }

    public function resolveForCondition(Node\Expr\Isset_ $node)
    {
        return array_values(
            array_filter(
                array_map(
                    fn ($var) => $this->resolveVarForCondition($var, $node),
                    $node->vars,
                ),
            ),
        );
    }

    public function resolveVarForCondition(Node\Expr $var, Node\Expr\Isset_ $node)
    {
        if ($this->scope->state()->canHandle($var)) {
            return Condition::from(
                $var,
                $this->scope->state()->getAtLine($var)?->type() ?? Type::mixed()
            )
                ->whenTrue(fn ($_, TypeContract $type) => $type->nullable(false))
                ->whenFalse(fn ($_, TypeContract $type) => $type->nullable(true));
        }

        if ($var instanceof Node\Expr\ArrayDimFetch) {
            $key = $this->fromOutsideOfCondition($var->dim);

            if ($key instanceof MixedType || ! property_exists($key, 'value') || $key->value === null) {
                // We don't know the key, so we can't unset the array key
                return null;
            }

            [$varToLookFor, $keys] = $this->resolveArrayVarAndKeys($var);

            return Condition::from(
                $var,
                $this->scope->state()->getAtLine($varToLookFor)?->type() ?? Type::mixed()
            )
                ->whenTrue(fn ($_, TypeContract $type) => $type->nullable(false))
                ->whenFalse(fn () => $this->scope->state()->removeArrayKeyType($varToLookFor, $keys, Type::null(), $node));
        }

        return null;
    }
}
