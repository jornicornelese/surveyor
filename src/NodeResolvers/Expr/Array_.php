<?php

namespace Laravel\StaticAnalyzer\NodeResolvers\Expr;

use Laravel\StaticAnalyzer\NodeResolvers\AbstractResolver;
use Laravel\StaticAnalyzer\Types\Type;
use PhpParser\Node;

class Array_ extends AbstractResolver
{
    public function resolve(Node\Expr\Array_ $node)
    {
        $items = collect($node->items);

        $isList = $items->every(fn ($item) => $item->key === null);

        if ($isList) {
            return Type::array(
                $items->map(fn ($item) => $this->from($item->value))->unique()->values(),
            );
        }

        return Type::array(
            $items
                ->mapWithKeys(fn ($item) => [
                    $item->key->value ?? null => $this->from($item->value),
                ])
                ->toArray(),
        );
    }
}
