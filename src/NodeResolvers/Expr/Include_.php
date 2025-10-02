<?php

namespace Laravel\Surveyor\NodeResolvers\Expr;

use Laravel\Surveyor\Analyzer\Analyzer;
use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use Laravel\Surveyor\Types\StringType;
use Laravel\Surveyor\Types\Type;
use PhpParser\Node;

class Include_ extends AbstractResolver
{
    public function resolve(Node\Expr\Include_ $node)
    {
        $result = $this->from($node->expr);

        if (! Type::is($result, StringType::class)) {
            return Type::mixed();
        }

        if (! file_exists($result->value)) {
            return Type::mixed();
        }

        $analyzer = app(Analyzer::class)->analyze($result->value);
        $types = array_map(fn ($return) => $return['type'], $analyzer->analyzed()->returnTypes());

        return Type::union(...$types);
    }
}
