<?php

namespace Laravel\Surveyor\NodeResolvers\Shared;

use PhpParser\Node;

trait ResolvesArrays
{
    protected function resolveArrayVarAndKeys(
        Node\Expr\ArrayDimFetch $node,
    ) {
        $keys = [];
        $var = $node->var;

        while ($var instanceof Node\Expr\ArrayDimFetch) {
            $keys[] = $this->fromOutsideOfCondition($var->dim)->value;
            $var = $var->var;
        }

        $keys = array_reverse($keys);

        return [$var, $keys];
    }
}
