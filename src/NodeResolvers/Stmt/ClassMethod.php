<?php

namespace Laravel\StaticAnalyzer\NodeResolvers\Stmt;

use Laravel\StaticAnalyzer\Analysis\ReturnTypeAnalyzer;
use Laravel\StaticAnalyzer\Analysis\VariableAnalyzer;
use Laravel\StaticAnalyzer\NodeResolvers\AbstractResolver;
use Laravel\StaticAnalyzer\Result\ClassMethodDeclaration;
use PhpParser\Node;

class ClassMethod extends AbstractResolver
{
    protected $variableTracker;

    public function resolve(Node\Stmt\ClassMethod $node)
    {
        $this->getVariableTracker($node);

        return (new ClassMethodDeclaration(
            name: $node->name->toString(),
            parameters: $this->getAllParameters($node),
            returnTypes: $this->getAllReturnTypes($node),
        ))->fromNode($node);
    }

    protected function getAllParameters(Node\Stmt\ClassMethod $node)
    {
        if (count($node->params) === 0) {
            return [];
        }

        return array_map(fn ($n) => $this->from($n), $node->params);
    }

    protected function getVariableTracker(Node\Stmt\ClassMethod $node)
    {
        $this->variableTracker ??= $this->getAllVariables($node);
        $this->variableTracker->setCurrent();

        return $this->variableTracker;
    }

    protected function getAllVariables(Node\Stmt\ClassMethod $node)
    {
        $analyzer = app(VariableAnalyzer::class);

        return $analyzer->analyze($node);
    }

    protected function getAllReturnTypes(Node\Stmt\ClassMethod $node)
    {
        $analyzer = app(ReturnTypeAnalyzer::class);

        dd($analyzer->analyze($node));

        return $analyzer->analyze($node);
    }
}
