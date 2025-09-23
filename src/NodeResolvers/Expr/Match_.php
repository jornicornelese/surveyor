<?php

namespace Laravel\Surveyor\NodeResolvers\Expr;

use Laravel\Surveyor\Debug\Debug;
use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use Laravel\Surveyor\NodeResolvers\Shared\CapturesConditionalChanges;
use PhpParser\Node;

class Match_ extends AbstractResolver
{
    use CapturesConditionalChanges;

    public function resolve(Node\Expr\Match_ $node)
    {
        $this->scope->startConditionAnalysis();
        $result = $this->from($node->cond);
        $this->scope->endConditionAnalysis();

        if ($result !== null) {
            // Debug::ddAndOpen($node, $result, 'result is not null in match');
        }

        // TODO: We're not doing anything with this yet, we... should
        $currentConditions = [];

        foreach ($node->arms as $arm) {
            if ($arm->conds === null) {
                continue;
            }

            foreach ($arm->conds as $cond) {
                $this->scope->startConditionAnalysis();
                $currentConditions[] = $this->from($cond);
                $this->scope->endConditionAnalysis();

                $this->startCapturing($arm);
                $this->from($arm->body);
                $this->capture($arm);
            }
        }

        return null;
    }
}
