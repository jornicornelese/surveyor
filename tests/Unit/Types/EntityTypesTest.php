<?php

use App\Http\Resources\UserResource;
use Laravel\Surveyor\Types\ClassType;
use Laravel\Surveyor\Types\Entities\InertiaRender;
use Laravel\Surveyor\Types\Entities\ResourceResponse;
use Laravel\Surveyor\Types\Entities\View;
use Laravel\Surveyor\Types\Type;

describe('View', function () {
    it('properly initializes parent ClassType value', function () {
        $view = new View('welcome', Type::array([]));

        expect($view->resolved())->toBe('Illuminate\View\View');
    });

    it('returns custom id based on view name and data', function () {
        $view = new View('welcome', Type::array([]));

        expect($view->id())->toBe('welcome::'.Type::array([])->id());
    });

    it('works correctly with Type::union', function () {
        $view = new View('welcome', Type::array([]));
        $classType = new ClassType('App\Models\User');

        $union = Type::union($view, $classType);

        expect($union)->toBeInstanceOf(Laravel\Surveyor\Types\UnionType::class);
    });
});

describe('InertiaRender', function () {
    it('properly initializes parent ClassType value', function () {
        $inertia = new InertiaRender('Dashboard', Type::array([]));

        expect($inertia->resolved())->toBe('Inertia\Response');
    });

    it('returns custom id based on view name and data', function () {
        $inertia = new InertiaRender('Dashboard', Type::array([]));

        expect($inertia->id())->toBe('Dashboard::'.Type::array([])->id());
    });

    it('works correctly with Type::union', function () {
        $inertia = new InertiaRender('Dashboard', Type::array([]));
        $classType = new ClassType('App\Models\User');

        $union = Type::union($inertia, $classType);

        expect($union)->toBeInstanceOf(Laravel\Surveyor\Types\UnionType::class);
    });
});

describe('ResourceResponse', function () {
    it('initializes parent ClassType with resource class for single resource', function () {
        $model = new ClassType('App\Models\User');

        $response = new ResourceResponse(
            resourceClass: UserResource::class,
            wrappedData: $model,
            isCollection: false,
            resource: $model,
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
            resource: $model,
        );

        expect($response->resolved())->toBe('Illuminate\Http\Resources\Json\AnonymousResourceCollection');
    });

    it('returns custom id based on resource class and wrapped data', function () {
        $model = new ClassType('App\Models\User');

        $response = new ResourceResponse(
            resourceClass: UserResource::class,
            wrappedData: $model,
            isCollection: false,
            resource: $model,
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
            resource: $model,
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
            resource: $model,
        );

        expect($response->resource)->toBeInstanceOf(ClassType::class);
        expect($response->resource->value)->toBe('App\Models\User');
        expect($response->wrappedData->value)->toBe('Illuminate\Pagination\LengthAwarePaginator');
    });

    it('works correctly with Type::union', function () {
        $model = new ClassType('App\Models\User');

        $response = new ResourceResponse(
            resourceClass: UserResource::class,
            wrappedData: $model,
            isCollection: false,
            resource: $model,
        );

        $classType = new ClassType('App\Models\Post');
        $union = Type::union($response, $classType);

        expect($union)->toBeInstanceOf(Laravel\Surveyor\Types\UnionType::class);
    });
});
