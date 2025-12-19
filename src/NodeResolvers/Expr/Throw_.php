<?php

namespace Laravel\Surveyor\NodeResolvers\Expr;

use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use PhpParser\Node;

class Throw_ extends AbstractResolver
{
    public function resolve(Node\Expr\Throw_ $node)
    {
        return null;
    }
}
