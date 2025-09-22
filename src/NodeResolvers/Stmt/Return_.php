<?php

namespace Laravel\Surveyor\NodeResolvers\Stmt;

use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use Laravel\Surveyor\Types\Type;
use PhpParser\Node;

class Return_ extends AbstractResolver
{
    public function resolve(Node\Stmt\Return_ $node)
    {
        $this->scope->state()->markSnapShotAsTerminated($node);

        $type = $this->from($node->expr);

        $result = $type === null ? Type::mixed() : Type::collapse($type);

        $this->scope->addReturnType($result, $node->getStartLine());

        return null;
    }
}
