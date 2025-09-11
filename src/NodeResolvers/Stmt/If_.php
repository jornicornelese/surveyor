<?php

namespace Laravel\Surveyor\NodeResolvers\Stmt;

use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use PhpParser\Node;

class If_ extends AbstractResolver
{
    public function resolve(Node\Stmt\If_ $node)
    {
        $this->scope->variables()->startSnapshot($node);

        // Analyze the condition for type narrowing
        $this->scope->startConditionAnalysis();
        $this->from($node->cond);
        $this->scope->endConditionAnalysis();

        // $changed = $this->tracker->endVariableSnapshot($ifStmt->getStartLine());
    }
}
