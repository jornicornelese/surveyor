<?php

namespace Laravel\Surveyor\NodeResolvers\Shared;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Request as RequestFacade;
use Laravel\Surveyor\Concerns\LazilyLoadsDependencies;
use Laravel\Surveyor\Types\ClassType;
use Laravel\Surveyor\Types\Contracts\Type as TypeContract;
use Laravel\Surveyor\Types\Entities\ResourceResponse;
use Laravel\Surveyor\Types\MixedType;
use Laravel\Surveyor\Types\StringType;
use Laravel\Surveyor\Types\Type;
use PhpParser\Node;

trait ResolvesMethodCalls
{
    use AddsValidationRules, LazilyLoadsDependencies, ResolvesClosureReturnTypes;

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

        if ($resolved = $this->resolveJsonResourceConditional($var, $methodName->value, $node)) {
            return $resolved;
        }

        return Type::union(
            ...$this->reflector->methodReturnType(
                $this->scope->getUse($var->value),
                $methodName->value,
                $node,
            ),
        );
    }

    /**
     * Map of JsonResource conditional method names to [valueArgIndex, defaultArgIndex].
     */
    protected function conditionalMethodArgIndices(string $method): ?array
    {
        return match ($method) {
            'when', 'unless', 'whenLoaded', 'whenCounted', 'whenHas', 'whenAppended',
            'whenExistsLoaded', 'whenPivotLoaded', 'mergeWhen', 'mergeUnless' => [1, 2],
            'whenAggregated' => [3, 4],
            'whenPivotLoadedAs' => [2, 3],
            'whenNull', 'whenNotNull' => [0, 1],
            'merge' => [0, null],
            default => null,
        };
    }

    protected function resolveJsonResourceConditional(ClassType $var, string $methodName, Node $node): ?TypeContract
    {
        $argIndices = $this->conditionalMethodArgIndices($methodName);

        if ($argIndices === null) {
            return null;
        }

        if (! class_exists($var->resolved()) || ! is_subclass_of($var->resolved(), JsonResource::class)) {
            return null;
        }

        [$valueIndex, $defaultIndex] = $argIndices;
        $args = $node->args;

        if (! isset($args[$valueIndex])) {
            // 1-arg form like whenLoaded('relation') — we don't know the type statically
            return Type::mixed()->optional();
        }

        $valueType = $this->resolveConditionalArg($args[$valueIndex]->value);

        $hasDefault = $defaultIndex !== null && isset($args[$defaultIndex]);

        if ($hasDefault) {
            $defaultType = $this->resolveConditionalArg($args[$defaultIndex]->value);

            return Type::union($valueType, $defaultType);
        }

        // No default — field may be absent from JSON output
        $valueType->optional();

        return $valueType;
    }

    protected function resolveConditionalArg(Node\Expr $expr): TypeContract
    {
        if ($expr instanceof Node\Expr\ArrowFunction || $expr instanceof Node\Expr\Closure) {
            return $this->resolveClosureReturnType($expr) ?? Type::mixed();
        }

        return $this->from($expr);
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
