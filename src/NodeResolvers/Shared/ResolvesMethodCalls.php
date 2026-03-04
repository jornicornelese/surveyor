<?php

namespace Laravel\Surveyor\NodeResolvers\Shared;

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
    use AddsValidationRules, LazilyLoadsDependencies, ResolvesClosureReturnTypes, ResolvesModelFromExpression;

    protected function resolveMethodCall(Node\Expr\MethodCall|Node\Expr\NullsafeMethodCall $node)
    {
        $var = $this->from($node->var);

        // Resolve method name: for static Identifier nodes, use the raw name to avoid
        // Type::string() treating method names that happen to match a global function
        // (e.g. the Illuminate `when()` helper) as ClassType instead of StringType.
        $methodName = $node->name instanceof Node\Identifier
            ? new StringType($node->name->name)
            : $this->from($node->name);

        if (! Type::is($methodName, StringType::class) || $methodName->value === null) {
            return Type::mixed();
        }

        if (in_array($methodName->value, ['toResource', 'toResourceCollection'])
            && count($node->args) > 0
            && $node->args[0]->value instanceof Node\Expr\ClassConstFetch
            && $node->args[0]->value->class instanceof Node\Name) {
            $resourceClass = $this->scope->getUse($node->args[0]->value->class->toString());

            if (class_exists($resourceClass) && is_subclass_of($resourceClass, JsonResource::class)) {
                $wrappedData = $var instanceof ClassType ? $var : Type::mixed();
                $model = $var instanceof ClassType
                    ? ($this->resolveModelFromExpression($node->var) ?? $var)
                    : Type::mixed();

                return new ResourceResponse(
                    resourceClass: $resourceClass,
                    wrappedData: $wrappedData,
                    isCollection: $methodName->value === 'toResourceCollection',
                    resource: $model,
                );
            }
        }

        if ($var instanceof MixedType || ! $var instanceof ClassType) {
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
            // whenCounted('relation') — always returns an integer count
            if ($methodName === 'whenCounted') {
                return Type::int()->optional();
            }

            // whenExistsLoaded('relation') — always returns a bool (withExists() casts to bool)
            if ($methodName === 'whenExistsLoaded') {
                return Type::bool()->optional();
            }

            // All other 1-arg forms (whenLoaded, whenHas, etc.) — unknown type statically
            return Type::mixed()->optional();
        }

        $valueType = $this->resolveConditionalArg($args[$valueIndex]->value);

        $hasDefault = $defaultIndex !== null && isset($args[$defaultIndex]);

        if ($hasDefault) {
            $defaultType = $this->resolveConditionalArg($args[$defaultIndex]->value);

            // If either type is unresolved (MixedType), return mixed rather than
            // letting Type::union() filter it out and produce a misleadingly specific type.
            if ($valueType instanceof MixedType || $defaultType instanceof MixedType) {
                return Type::mixed();
            }

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
}
