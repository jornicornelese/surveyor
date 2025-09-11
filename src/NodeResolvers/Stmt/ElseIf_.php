<?php

namespace Laravel\Surveyor\NodeResolvers\Stmt;

use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use PhpParser\Node;

class ElseIf_ extends AbstractResolver
{
    public function resolve(Node\Stmt\ElseIf_ $node)
    {
        $this->scope->variables()->startSnapshot($node);
        // $changed = $this->tracker->endVariableSnapshot($elseif->getStartLine());
    }
}
