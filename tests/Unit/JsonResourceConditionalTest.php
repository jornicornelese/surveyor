<?php

use Laravel\Surveyor\Analyzer\AnalyzedCache;
use Laravel\Surveyor\Types\ArrayType;
use Laravel\Surveyor\Types\IntType;
use Laravel\Surveyor\Types\MixedType;
use Laravel\Surveyor\Types\StringType;
use Laravel\Surveyor\Types\UnionType;

uses()->group('integration');

beforeEach(function () {
    AnalyzedCache::clear();
});

afterEach(function () {
    AnalyzedCache::clear();
});

/**
 * Resolves the toArray() return type for ConditionalUserResource
 * and returns the value for the given key.
 */
function conditionalField(string $key): mixed
{
    $result = analyzeFile('app/Http/Resources/ConditionalUserResource.php')->result();

    return $result->getMethod('toArray')->returnType()->value[$key] ?? null;
}

describe('when()', function () {
    it('resolves to optional value type when no default is given', function () {
        $type = conditionalField('secret');

        expect($type)->toBeInstanceOf(StringType::class);
        expect($type->isOptional())->toBeTrue();
    });

    it('resolves to union of value and default when default is given', function () {
        $type = conditionalField('role');

        expect($type)->toBeInstanceOf(UnionType::class);
        expect($type->types[0])->toBeInstanceOf(StringType::class);
        expect($type->types[1])->toBeInstanceOf(StringType::class);
    });

    it('resolves closure value to its return type (optional)', function () {
        $type = conditionalField('computed');

        expect($type)->toBeInstanceOf(StringType::class);
        expect($type->isOptional())->toBeTrue();
    });
});

describe('unless()', function () {
    it('resolves to optional value type (same indices as when)', function () {
        $type = conditionalField('unless_field');

        expect($type)->toBeInstanceOf(StringType::class);
        expect($type->isOptional())->toBeTrue();
    });
});

describe('whenLoaded()', function () {
    it('resolves to optional mixed when only the relation name is given', function () {
        $type = conditionalField('posts');

        expect($type)->toBeInstanceOf(MixedType::class);
        expect($type->isOptional())->toBeTrue();
    });

    it('resolves closure value to its return type (optional)', function () {
        $type = conditionalField('posts_loaded');

        expect($type)->toBeInstanceOf(StringType::class);
        expect($type->isOptional())->toBeTrue();
    });
});

describe('whenNotNull()', function () {
    it('resolves to optional value type when no default is given', function () {
        $type = conditionalField('nullable_field');

        expect($type)->toBeInstanceOf(StringType::class);
        expect($type->isOptional())->toBeTrue();
    });

    it('resolves to union of value and default when default is given', function () {
        $type = conditionalField('display_name');

        expect($type)->toBeInstanceOf(UnionType::class);
        expect($type->types[0])->toBeInstanceOf(StringType::class);
        expect($type->types[1])->toBeInstanceOf(StringType::class);
    });
});

describe('whenNull()', function () {
    it('resolves to union of value and default (same indices as whenNotNull)', function () {
        $type = conditionalField('null_field');

        expect($type)->toBeInstanceOf(UnionType::class);
        expect($type->types[0])->toBeInstanceOf(StringType::class);
        expect($type->types[1])->toBeInstanceOf(StringType::class);
    });
});

describe('whenCounted()', function () {
    it('resolves to optional int when only the relation name is given', function () {
        $type = conditionalField('posts_count');

        expect($type)->toBeInstanceOf(IntType::class);
        expect($type->isOptional())->toBeTrue();
    });
});

describe('whenHas()', function () {
    it('resolves to optional mixed when only the attribute name is given', function () {
        $type = conditionalField('bio');

        expect($type)->toBeInstanceOf(MixedType::class);
        expect($type->isOptional())->toBeTrue();
    });
});

describe('whenAppended()', function () {
    it('resolves to optional mixed when only the attribute name is given', function () {
        $type = conditionalField('appended');

        expect($type)->toBeInstanceOf(MixedType::class);
        expect($type->isOptional())->toBeTrue();
    });
});

describe('whenAggregated()', function () {
    it('resolves to optional mixed when no value arg is given (3-arg form)', function () {
        $type = conditionalField('words_avg');

        expect($type)->toBeInstanceOf(MixedType::class);
        expect($type->isOptional())->toBeTrue();
    });

    it('resolves closure value to its return type (optional) in the 4-arg form', function () {
        $type = conditionalField('words_sum');

        expect($type)->toBeInstanceOf(IntType::class);
        expect($type->isOptional())->toBeTrue();
    });
});

describe('whenPivotLoaded()', function () {
    it('resolves closure value to its return type (optional)', function () {
        $type = conditionalField('expires_at');

        expect($type)->toBeInstanceOf(StringType::class);
        expect($type->isOptional())->toBeTrue();
    });
});

describe('whenPivotLoadedAs()', function () {
    it('resolves closure value to its return type (optional)', function () {
        $type = conditionalField('pivot_field');

        expect($type)->toBeInstanceOf(StringType::class);
        expect($type->isOptional())->toBeTrue();
    });
});

describe('merge()', function () {
    it('resolves to optional array type', function () {
        $type = conditionalField('merged');

        expect($type)->toBeInstanceOf(ArrayType::class);
        expect($type->isOptional())->toBeTrue();
    });
});

describe('mergeWhen()', function () {
    it('resolves to optional array type', function () {
        $type = conditionalField('merged_when');

        expect($type)->toBeInstanceOf(ArrayType::class);
        expect($type->isOptional())->toBeTrue();
    });
});

describe('mergeUnless()', function () {
    it('resolves to optional array type', function () {
        $type = conditionalField('merged_unless');

        expect($type)->toBeInstanceOf(ArrayType::class);
        expect($type->isOptional())->toBeTrue();
    });
});

describe('whenExistsLoaded()', function () {
    it('resolves to optional bool when only the relation name is given', function () {
        $type = conditionalField('posts_exists');

        expect($type)->toBeInstanceOf(\Laravel\Surveyor\Types\BoolType::class);
        expect($type->isOptional())->toBeTrue();
    });

    it('resolves closure value to its return type (optional)', function () {
        $type = conditionalField('posts_exists_loaded');

        expect($type)->toBeInstanceOf(StringType::class);
        expect($type->isOptional())->toBeTrue();
    });
});
