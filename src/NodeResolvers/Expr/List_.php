<?php

namespace Laravel\Surveyor\NodeResolvers\Expr;

use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use PhpParser\Node;

class List_ extends AbstractResolver
{
    public function resolve(Node\Expr\List_ $node)
    {
        return null;
    }
}
