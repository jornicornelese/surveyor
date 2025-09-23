<?php

namespace Laravel\Surveyor\NodeResolvers\Expr;

use Laravel\Surveyor\Analysis\Condition;
use Laravel\Surveyor\Debug\Debug;
use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use Laravel\Surveyor\Types\Contracts\Type as TypeContract;
use Laravel\Surveyor\Types\MixedType;
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
        if (! $var instanceof Node\Expr\ArrayDimFetch) {
            if ($this->scope->state()->getAtLine($var) === null) {
                dd($var, $this->scope);
            }

            return Condition::from(
                $var,
                $this->scope->state()->getAtLine($var)->type()
            )
                ->whenTrue(fn ($_, TypeContract $type) => $type->nullable(false))
                ->whenFalse(fn ($_, TypeContract $type) => $type->nullable(true));
        }

        $key = $this->fromOutsideOfCondition($var->dim);

        if ($key instanceof MixedType) {
            return null;
        }

        if (! property_exists($key, 'value')) {
            Debug::ddAndOpen($key, $node, $var, 'unknown key');
        }

        if ($key->value === null) {
            // We don't know the key, so we can't unset the array key
            return null;
        }

        return Condition::from(
            $var,
            $this->scope->state()->getAtLine($var->var)?->type() ?? Type::mixed()
        )
            ->whenTrue(fn ($_, TypeContract $type) => $type->nullable(false))
            ->whenFalse(fn () => $this->scope->state()->removeArrayKeyType($var->var, $key->value, Type::null(), $node));
    }
}
