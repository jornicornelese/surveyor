<?php

namespace Laravel\Surveyor\NodeResolvers\Shared;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Request as RequestFacade;
use Laravel\Surveyor\Concerns\LazilyLoadsDependencies;
use Laravel\Surveyor\Types\ClassType;
use Laravel\Surveyor\Types\Entities\ResourceResponse;
use Laravel\Surveyor\Types\MixedType;
use Laravel\Surveyor\Types\StringType;
use Laravel\Surveyor\Types\Type;
use PhpParser\Node;

trait ResolvesMethodCalls
{
    use AddsValidationRules, LazilyLoadsDependencies;

    protected function resolveMethodCall(Node\Expr\MethodCall|Node\Expr\NullsafeMethodCall $node)
    {
        $var = $this->from($node->var);

        if ($var instanceof MixedType || ! $var instanceof ClassType) {
            return Type::mixed();
        }

        $methodName = $this->from($node->name);

        if (! Type::is($methodName, StringType::class) || $methodName->value === null) {
            return Type::mixed();
        }

        switch ($var->value) {
            case Request::class:
            case RequestFacade::class:
                if ($methodName->value === 'validate') {
                    $this->addValidationRules($node->args[0]->value);
                }

                if ($methodName->value === 'user' && $requestUserType = $this->getResolver()->requestUserType()) {
                    return $requestUserType;
                }
                break;
        }

        if (in_array($methodName->value, ['toResource', 'toResourceCollection'])
            && count($node->args) > 0) {
            $resourceClassArg = $this->from($node->args[0]->value);
            if ($resourceClassArg instanceof ClassType
                && class_exists($resourceClassArg->resolved())
                && is_subclass_of($resourceClassArg->resolved(), JsonResource::class)) {
                $isCollection = $methodName->value === 'toResourceCollection';
                $model = $this->resolveModelFromExpression($node->var) ?? $var;

                return new ResourceResponse(
                    resourceClass: $resourceClassArg->resolved(),
                    wrappedData: $var,
                    isCollection: $isCollection,
                    model: $model,
                );
            }
        }

        return Type::union(
            ...$this->reflector->methodReturnType(
                $this->scope->getUse($var->value),
                $methodName->value,
                $node,
            ),
        );
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
