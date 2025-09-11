<?php

namespace Laravel\Surveyor\Visitors;

use Laravel\Surveyor\Analysis\Scope;
use Laravel\Surveyor\Debug\Debug;
use Laravel\Surveyor\Resolvers\NodeResolver;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class TypeResolver extends NodeVisitorAbstract
{
    protected Scope $scope;

    public function __construct(
        protected NodeResolver $resolver,
    ) {
        $this->scope = new Scope;
    }

    public function scope()
    {
        return $this->scope;
    }

    public function enterNode(Node $node)
    {
        Debug::log('❗ Entering Node: '.$node->getType().' '.$node->getStartLine());

        // try {
        [$resolved, $scope] = $this->resolver->fromWithScope($node, $this->scope);
        // } catch (\Throwable $e) {
        //     dd($node, $e->getMessage());
        // }

        $this->scope = $scope;

        // return $resolved;
    }

    public function leaveNode(Node $node)
    {
        $this->scope = $this->resolver->exitNode($node, $this->scope);
    }
}
