<?php

namespace Laravel\Surveyor\NodeResolvers\Name;

use Laravel\Surveyor\Debug\Debug;
use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use Laravel\Surveyor\Support\Util;
use Laravel\Surveyor\Types\Type;
use PhpParser\Node;

class FullyQualified extends AbstractResolver
{
    public function resolve(Node\Name\FullyQualified $node)
    {
        return $this->resolveName($node);
    }

    public function resolveForCondition(Node\Name\FullyQualified $node)
    {
        return $this->resolveName($node);
    }

    protected function resolveName(Node\Name\FullyQualified $node)
    {
        // Debug::interested($node->getAttribute('originalName')->name === 'Request');

        $className = Util::resolveValidClass($node->toString(), $this->scope);

        if (! Util::isClassOrInterface($className) && $node->toString() !== $node->getAttribute('originalName')) {
            $className = $this->scope->resolveBuggyUse($node->getAttribute('originalName'));
        }

        // Debug::dumpIfInterested([$className, class_exists($className), $node->toString(), $this->scope->entityName()]);

        return Type::from($className);
    }
}
