<?php

namespace Laravel\Surveyor\NodeResolvers\Expr;

use Laravel\Surveyor\Debug\Debug;
use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use Laravel\Surveyor\Types\Type;
use PhpParser\Node;

class Ternary extends AbstractResolver
{
    public function resolve(Node\Expr\Ternary $node)
    {
        if ($node->if === null) {
            // e.g. ?:
            return $this->from($node->else);
        }

        Debug::interested();
        // Analyze the condition for type narrowing
        $this->scope->startConditionAnalysis();
        $result = $this->from($node->cond);
        $this->scope->endConditionAnalysis();

        dd($this->scope->variables(), $node->cond, $result);

        return Type::union(
            $this->from($node->if),
            $this->from($node->else),
        );
    }
}
