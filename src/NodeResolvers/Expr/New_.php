<?php

namespace Laravel\Surveyor\NodeResolvers\Expr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use Laravel\Surveyor\Types\ClassType;
use Laravel\Surveyor\Types\Entities\ResourceResponse;
use Laravel\Surveyor\Types\Type;
use PhpParser\Node;

class New_ extends AbstractResolver
{
    public function resolve(Node\Expr\New_ $node)
    {
        $type = $this->from($node->class);

        if (! property_exists($type, 'value') || $type->value === null) {
            // We couldn't figure it out
            return Type::mixed();
        }

        $classType = new ClassType($this->scope->getUse($type->value));

        $classType->setConstructorArguments(array_map(
            fn ($arg) => $this->from($arg->value),
            $node->args,
        ));

        if (class_exists($classType->resolved())
            && is_subclass_of($classType->resolved(), JsonResource::class)
            && count($node->args) > 0) {
            $wrappedData = $this->from($node->args[0]->value);
            $model = $this->resolveModelFromExpression($node->args[0]->value) ?? $wrappedData;

            return new ResourceResponse(
                resourceClass: $classType->resolved(),
                wrappedData: $wrappedData,
                isCollection: false,
                model: $model,
            );
        }

        return $classType;
    }

    public function resolveForCondition(Node\Expr\New_ $node)
    {
        return $this->resolve($node);
    }

    protected function resolveModelFromExpression(Node\Expr $expr): ?ClassType
    {
        while ($expr instanceof Node\Expr\MethodCall || $expr instanceof Node\Expr\NullsafeMethodCall) {
            $expr = $expr->var;
        }

        if ($expr instanceof Node\Expr\StaticCall && $expr->class instanceof Node\Name) {
            $className = $this->scope->getUse($expr->class->toString());
            $classType = new ClassType($className);

            if (class_exists($classType->resolved())
                && is_subclass_of($classType->resolved(), Model::class)) {
                return $classType;
            }
        }

        return null;
    }
}
