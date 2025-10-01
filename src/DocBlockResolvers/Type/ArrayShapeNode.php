<?php

namespace Laravel\Surveyor\DocBlockResolvers\Type;

use Laravel\Surveyor\DocBlockResolvers\AbstractResolver;
use Laravel\Surveyor\Types\Type;
use PHPStan\PhpDocParser\Ast;

class ArrayShapeNode extends AbstractResolver
{
    public function resolve(Ast\Type\ArrayShapeNode $node)
    {
        $items = [];

        foreach ($node->items as $item) {
            [$key, $value] = $this->resolveItem($item);

            if ($key === null) {
                $items[] = $value;
            } else {
                $items[$key->value] = $value;
            }
        }

        return Type::array($items);
    }

    protected function resolveItem(Ast\Type\ArrayShapeItemNode $item)
    {
        $key = $item->keyName ? $this->from($item->keyName) : null;
        $value = $this->from($item->valueType);

        if ($item->optional) {
            $value = $key ? $key->optional() : $value->optional();
        }

        return [$key, $value];
    }
}
