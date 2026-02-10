<?php

use App\Http\Resources\UserResource;
use Laravel\Surveyor\Analyzer\AnalyzedCache;
use Laravel\Surveyor\Types\ClassType;
use Laravel\Surveyor\Types\Entities\ResourceResponse;

uses()->group('integration');

beforeEach(function () {
    AnalyzedCache::clear();
});

afterEach(function () {
    AnalyzedCache::clear();
});

describe('new Resource() via New_ resolver', function () {
    it('resolves new UserResource() as ResourceResponse', function () {
        $result = analyzeFile('app/Http/Controllers/ResourceController.php')->result();

        $method = $result->getMethod('show');
        $returnType = $method->returnType();

        expect($returnType)->toBeInstanceOf(ResourceResponse::class);
        expect($returnType->resourceClass)->toBe(UserResource::class);
        expect($returnType->isCollection)->toBeFalse();
        expect($returnType->model)->toBeInstanceOf(ClassType::class);
    });
});

describe('Resource::make() via StaticCall resolver', function () {
    it('resolves UserResource::make() as ResourceResponse', function () {
        $result = analyzeFile('app/Http/Controllers/ResourceController.php')->result();

        $method = $result->getMethod('made');
        $returnType = $method->returnType();

        expect($returnType)->toBeInstanceOf(ResourceResponse::class);
        expect($returnType->resourceClass)->toBe(UserResource::class);
        expect($returnType->isCollection)->toBeFalse();
        expect($returnType->model)->toBeInstanceOf(ClassType::class);
    });
});

describe('Resource::collection() via StaticCall resolver', function () {
    it('resolves UserResource::collection(User::paginate()) as paginated ResourceResponse', function () {
        $result = analyzeFile('app/Http/Controllers/ResourceController.php')->result();

        $method = $result->getMethod('paginated');
        $returnType = $method->returnType();

        expect($returnType)->toBeInstanceOf(ResourceResponse::class);
        expect($returnType->resourceClass)->toBe(UserResource::class);
        expect($returnType->isCollection)->toBeTrue();
        expect($returnType->model)->toBeInstanceOf(ClassType::class);
        expect($returnType->model->resolved())->toBe('App\Models\User');
    });

    it('resolves UserResource::collection(User::all()) and extracts model', function () {
        $result = analyzeFile('app/Http/Controllers/ResourceController.php')->result();

        $method = $result->getMethod('collection');
        $returnType = $method->returnType();

        expect($returnType)->toBeInstanceOf(ResourceResponse::class);
        expect($returnType->resourceClass)->toBe(UserResource::class);
        expect($returnType->isCollection)->toBeTrue();
        expect($returnType->model)->toBeInstanceOf(ClassType::class);
        expect($returnType->model->resolved())->toBe('App\Models\User');
    });
});
