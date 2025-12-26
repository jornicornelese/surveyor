<?php

use Laravel\Surveyor\Analyzer\AnalyzedCache;
use Laravel\Surveyor\Analyzer\Analyzer;
use Laravel\Surveyor\Types\ArrayType;
use Laravel\Surveyor\Types\IntType;
use Laravel\Surveyor\Types\StringType;

uses()->group('integration');

beforeEach(function () {
    AnalyzedCache::clear();
});

afterEach(function () {
    AnalyzedCache::clear();
});

describe('array spread operator', function () {
    it('handles spread operator in keyed array', function () {
        $fixture = createPhpFixture('
namespace App;

class SpreadTest
{
    public function test(): array
    {
        $ar1 = ["first" => "a", "second" => "b"];
        $result = [...$ar1, "name" => "Joe", "age" => 25];

        return $result;
    }
}');

        $analyzer = app(Analyzer::class);
        $result = $analyzer->analyze($fixture)->result();

        $method = $result->getMethod('test');
        $returnType = $method->returnType();

        expect($returnType)->toBeInstanceOf(ArrayType::class);
        expect($returnType->value)->toHaveKey('first');
        expect($returnType->value)->toHaveKey('second');
        expect($returnType->value)->toHaveKey('name');
        expect($returnType->value)->toHaveKey('age');
        expect($returnType->value['first'])->toBeInstanceOf(StringType::class);
        expect($returnType->value['second'])->toBeInstanceOf(StringType::class);
        expect($returnType->value['name'])->toBeInstanceOf(StringType::class);
        expect($returnType->value['age'])->toBeInstanceOf(IntType::class);

        unlink($fixture);
    });

    it('handles spread operator in list array', function () {
        $fixture = createPhpFixture('
namespace App;

class SpreadListTest
{
    public function test(): array
    {
        $ar1 = ["a", "b"];
        $result = [...$ar1, "c", "d"];

        return $result;
    }
}');

        $analyzer = app(Analyzer::class);
        $result = $analyzer->analyze($fixture)->result();

        $method = $result->getMethod('test');
        $returnType = $method->returnType();

        expect($returnType)->toBeInstanceOf(ArrayType::class);
        expect($returnType->isList())->toBeTrue();
        // We expect 4 items: 2 from spread + 2 new
        expect(count($returnType->value))->toBe(4);

        unlink($fixture);
    });

    it('handles multiple spread operators in list array', function () {
        $fixture = createPhpFixture('
namespace App;

class MultiSpreadTest
{
    public function test(): array
    {
        $ar1 = ["a"];
        $ar2 = ["b"];
        $result = [...$ar1, ...$ar2, "c"];

        return $result;
    }
}');

        $analyzer = app(Analyzer::class);
        $result = $analyzer->analyze($fixture)->result();

        $method = $result->getMethod('test');
        $returnType = $method->returnType();

        expect($returnType)->toBeInstanceOf(ArrayType::class);
        expect($returnType->isList())->toBeTrue();
        // 1 from $ar1 + 1 from $ar2 + 1 literal = 3
        expect(count($returnType->value))->toBe(3);

        unlink($fixture);
    });

    it('handles spread operator with only spreads in keyed array', function () {
        $fixture = createPhpFixture('
namespace App;

class OnlySpreadTest
{
    public function test(): array
    {
        $ar1 = ["name" => "Joe"];
        $ar2 = ["age" => 25];
        $result = [...$ar1, ...$ar2];

        return $result;
    }
}');

        $analyzer = app(Analyzer::class);
        $result = $analyzer->analyze($fixture)->result();

        $method = $result->getMethod('test');
        $returnType = $method->returnType();

        expect($returnType)->toBeInstanceOf(ArrayType::class);
        expect($returnType->value)->toHaveKey('name');
        expect($returnType->value)->toHaveKey('age');

        unlink($fixture);
    });
});
