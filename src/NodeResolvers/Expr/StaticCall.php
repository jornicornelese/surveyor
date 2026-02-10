<?php

namespace Laravel\Surveyor\NodeResolvers\Expr;

use Illuminate\Support\Facades\Validator;
use Laravel\Surveyor\Analysis\Condition;
use Laravel\Surveyor\NodeResolvers\AbstractResolver;
use Laravel\Surveyor\NodeResolvers\Shared\AddsValidationRules;
use Laravel\Surveyor\Support\Util;
use Laravel\Surveyor\Types\ClassType;
use Laravel\Surveyor\Types\Contracts\MultiType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Laravel\Surveyor\Types\Entities\InertiaRender;
use Laravel\Surveyor\Types\Entities\ResourceResponse;
use Laravel\Surveyor\Types\Entities\View;
use Laravel\Surveyor\Types\StringType;
use Laravel\Surveyor\Types\Type;
use Laravel\Surveyor\Types\UnionType;
use PhpParser\Node;

class StaticCall extends AbstractResolver
{
    use AddsValidationRules;

    public function resolve(Node\Expr\StaticCall $node)
    {
        $class = $this->from($node->class);
        $method = $node->name instanceof Node\Identifier ? $node->name->name : $this->from($node->name);

        if ($method === 'macro') {
            if (Type::is($class, ClassType::class)) {
                $this->handleMacro($class, $node);
            }
        }

        if ($class instanceof ClassType && $class->value === Validator::class && $method === 'make') {
            $this->addValidationRules($node->args[1]->value);
        }

        if ($class instanceof UnionType) {
            $class = $this->resolveUnion($class);
        }

        if ($class instanceof StringType) {
            return ($class->value === null) ? null : Type::mixed();
        }

        if ($method instanceof MultiType) {
            $returnTypes = [];

            foreach ($method->types as $type) {
                $returnTypes = array_merge(
                    $returnTypes,
                    $this->reflector->methodReturnType($class, $type->value, $node),
                );
            }

            return Type::union(...$returnTypes);
        }

        if ($class instanceof Condition) {
            $class = $class->type;
        }

        if (in_array($method, ['collection', 'make'])
            && $class instanceof ClassType
            && class_exists($class->resolved())
            && is_subclass_of($class->resolved(), JsonResource::class)
            && count($node->args) > 0) {
            $wrappedData = $this->from($node->args[0]->value);
            $model = $this->resolveModelFromExpression($node->args[0]->value) ?? $wrappedData;

            return new ResourceResponse(
                resourceClass: $class->resolved(),
                wrappedData: $wrappedData,
                isCollection: $method === 'collection',
                model: $model,
            );
        }

        $returnTypes = array_merge(
            $this->handleEntities($class, $method, $node),
            $this->reflector->methodReturnType($class, $method, $node),
        );

        return Type::union(...$returnTypes);
    }

    protected function handleMacro(ClassType $class, Node\Expr\StaticCall $node): void
    {
        $macroName = $this->from($node->args[0]->value);
        $macroResolution = $this->from($node->args[1]->value);

        if (Type::is($macroName, StringType::class) && $macroName->value !== null) {
            $this->scope->addMacro($class->value, $macroName->value, $macroResolution);
        }
    }

    protected function handleEntities(ClassType $class, string $method, Node\Expr\StaticCall $node): array
    {
        return match ($class->value) {
            'Inertia\Inertia' => $this->handleInertiaEntity($method, $node),
            'Illuminate\Support\Facades\View' => $this->handleViewEntity($method, $node),
            default => [],
        };
    }

    protected function handleInertiaEntity(string $method, Node\Expr\StaticCall $node): array
    {
        if ($method !== 'render') {
            return [];
        }

        $args = array_map(fn ($arg) => $this->from($arg->value), $node->getArgs());

        return [
            new InertiaRender(
                $args[0]->value,
                $args[1] ?? Type::arrayShape(Type::string(), Type::mixed()),
            ),
        ];
    }

    protected function handleViewEntity(string $method, Node\Expr\StaticCall $node): array
    {
        if ($method !== 'render') {
            return [];
        }

        $args = array_map(fn ($arg) => $this->from($arg->value), $node->getArgs());

        return [
            new View(
                $args[0]->value,
                $args[1] ?? Type::arrayShape(Type::string(), Type::mixed()),
            ),
        ];
    }

    public function resolveForCondition(Node\Expr\StaticCall $node)
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

    protected function resolveUnion(UnionType $union)
    {
        foreach ($union->types as $type) {
            if ($type instanceof ClassType) {
                return $type;
            }

            if ($type instanceof StringType) {
                if (Util::isClassOrInterface($type->value)) {
                    return new ClassType($type->value);
                }

                $templateTag = $this->scope->getTemplateTag($type->value);

                if ($templateTag) {
                    return $templateTag->bound;
                }
            }
        }

        return null;
    }
}
