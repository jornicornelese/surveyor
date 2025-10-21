<?php

namespace Laravel\Surveyor\Result;

use PhpParser\NodeAbstract;

abstract class AbstractResult
{
    protected int $startLine;

    protected int $endLine;

    // TODO: I don't think this is used?
    public function fromNode(NodeAbstract $node)
    {
        $this->startLine = $node->getStartLine();
        $this->endLine = $node->getEndLine();

        return $this;
    }
}
