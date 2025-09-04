<?php

namespace Laravel\StaticAnalyzer\NodeResolvers\Expr;

use Laravel\StaticAnalyzer\NodeResolvers\AbstractResolver;
use Laravel\StaticAnalyzer\Types\ArrayShapeType;
use Laravel\StaticAnalyzer\Types\ArrayType;
use Laravel\StaticAnalyzer\Types\Type;
use PhpParser\Node;

class ArrayDimFetch extends AbstractResolver
{
    public function resolve(Node\Expr\ArrayDimFetch $node)
    {
        $var = $this->from($node->var);
        $dim = $node->dim === null ? Type::int() : $this->from($node->dim);

        if (! $var instanceof ArrayType && ! $var instanceof ArrayShapeType) {
            dd('ArrayDimFetch on non-array?', $var);
        }

        if ($var instanceof ArrayShapeType) {
            return $var->valueType;
        }

        if (property_exists($dim, 'value')) {
            return $var->value[$dim->value] ?? Type::mixed();
        }

        return Type::mixed();
    }
}
