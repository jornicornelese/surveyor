<?php

use App\Http\Resources\UserResource;
use Laravel\Surveyor\Types\ClassType;
use Laravel\Surveyor\Types\Entities\ResourceResponse;
use Laravel\Surveyor\Types\Type;

describe('ResourceResponse', function () {
    it('initializes parent ClassType with resource class for single resource', function () {
        $model = new ClassType('App\Models\User');

        $response = new ResourceResponse(
            resourceClass: UserResource::class,
            wrappedData: $model,
            isCollection: false,
            model: $model,
        );

        expect($response->resolved())->toBe(UserResource::class);
    });

    it('initializes parent ClassType with AnonymousResourceCollection for collection', function () {
        $model = new ClassType('App\Models\User');
        $wrappedData = new ClassType('Illuminate\Pagination\LengthAwarePaginator');

        $response = new ResourceResponse(
            resourceClass: UserResource::class,
            wrappedData: $wrappedData,
            isCollection: true,
            model: $model,
        );

        expect($response->resolved())->toBe('Illuminate\Http\Resources\Json\AnonymousResourceCollection');
    });

    it('returns custom id based on resource class and wrapped data', function () {
        $model = new ClassType('App\Models\User');

        $response = new ResourceResponse(
            resourceClass: UserResource::class,
            wrappedData: $model,
            isCollection: false,
            model: $model,
        );

        expect($response->id())->toBe(UserResource::class.'::single::'.$model->id());
    });

    it('includes collection in id for collection resources', function () {
        $model = new ClassType('App\Models\User');
        $wrappedData = new ClassType('Illuminate\Pagination\LengthAwarePaginator');

        $response = new ResourceResponse(
            resourceClass: UserResource::class,
            wrappedData: $wrappedData,
            isCollection: true,
            model: $model,
        );

        expect($response->id())->toBe(UserResource::class.'::collection::'.$wrappedData->id());
    });

    it('preserves model separately from wrappedData for collections', function () {
        $model = new ClassType('App\Models\User');
        $wrappedData = new ClassType('Illuminate\Pagination\LengthAwarePaginator');

        $response = new ResourceResponse(
            resourceClass: UserResource::class,
            wrappedData: $wrappedData,
            isCollection: true,
            model: $model,
        );

        expect($response->model)->toBeInstanceOf(ClassType::class);
        expect($response->model->value)->toBe('App\Models\User');
        expect($response->wrappedData->value)->toBe('Illuminate\Pagination\LengthAwarePaginator');
    });

    it('works correctly with Type::union', function () {
        $model = new ClassType('App\Models\User');

        $response = new ResourceResponse(
            resourceClass: UserResource::class,
            wrappedData: $model,
            isCollection: false,
            model: $model,
        );

        $classType = new ClassType('App\Models\Post');
        $union = Type::union($response, $classType);

        expect($union)->toBeInstanceOf(Laravel\Surveyor\Types\UnionType::class);
    });
});
