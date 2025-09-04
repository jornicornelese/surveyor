<?php

namespace Laravel\StaticAnalyzer\Types;

use Illuminate\Support\Collection;

class Type
{
    public static function mixed(): Contracts\Type
    {
        return new MixedType;
    }

    public static function array($value): Contracts\Type
    {
        return new ArrayType($value);
    }

    public static function is(Contracts\Type $type, string ...$classes): bool
    {
        foreach ($classes as $class) {
            if ($type instanceof $class) {
                return true;
            }
        }

        return false;
    }

    public static function string(?string $value = null): Contracts\Type
    {
        if ($value !== null && (class_exists($value) || interface_exists($value))) {
            return new ClassType($value);
        }

        return new StringType($value);
    }

    public static function isSame(Contracts\Type $type1, Contracts\Type $type2): bool
    {
        return $type1->id() === $type2->id();
    }

    public static function int(?int $value = null): Contracts\Type
    {
        return new IntType($value);
    }

    public static function bool(?bool $bool = null): Contracts\Type
    {
        return new BoolType($bool);
    }

    public static function arrayShape(Contracts\Type $keyType, Contracts\Type $itemType): Contracts\Type
    {
        return new ArrayShapeType($keyType, $itemType);
    }

    public static function null(): Contracts\Type
    {
        return new NullType;
    }

    public static function never(): Contracts\Type
    {
        return new NeverType;
    }

    public static function void(): Contracts\Type
    {
        return new VoidType;
    }

    public static function from(mixed $value): Contracts\Type
    {
        if ($value instanceof Contracts\Type) {
            return $value;
        }

        if ($value === null) {
            return self::null();
        }

        if (is_string($value)) {
            // TODO: Handle more types
            // - `array`
            // - `callable`
            // - `bool`
            // - `float`
            // - `int`
            // - `string`
            // - `iterable`
            // - `object`
            // - `mixed`
            if ($value === 'array') {
                return self::array([]);
            }

            if ($value === 'true') {
                return self::bool(true);
            }

            if ($value === 'false') {
                return self::bool(false);
            }

            if ($value === 'object') {
                return self::arrayShape(self::mixed(), self::mixed());
            }

            if ($value === 'void') {
                return self::void();
            }

            if (method_exists(self::class, $value)) {
                return self::$value();
            }

            return self::string($value);
        }

        dd('something else from', $value, debug_backtrace(limit: 3));
    }

    protected static function flattenUnion(array $args): Collection
    {
        return collect($args)->flatMap(
            fn ($type) => ($type instanceof UnionType)
                ? self::flattenUnion($type->types)
                : [$type]
        );
    }

    public static function union(...$args): Contracts\Type
    {
        $args = self::flattenUnion($args)
            ->filter()
            ->unique(fn ($type) => $type->toString())
            ->values();

        $nullType = $args->filter(fn ($type) => $type instanceof NullType);

        if ($nullType->isNotEmpty()) {
            $args = $args->map(fn ($type) => $type instanceof NullType ? null : $type->nullable())->filter()->values();
        }

        // Remove types that have a more specific counterpart
        $args = $args->filter(fn ($type) => ! $args->contains(
            fn ($otherType) => $type !== $otherType && $otherType->isMoreSpecificThan($type)
        ))->values();

        if ($args->count() === 1) {
            return $args->first();
        }

        return new UnionType($args->all());
    }

    public static function intersection(...$args): Contracts\Type
    {
        if (count($args) === 1) {
            return $args[0];
        }

        return new IntersectionType($args);
    }
}
