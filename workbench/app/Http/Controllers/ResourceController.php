<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ResourceController extends Controller
{
    // new UserResource($user) — New_ resolver
    public function show(): UserResource
    {
        return new UserResource(User::first());
    }

    // UserResource::make($user) — StaticCall resolver
    public function made(): UserResource
    {
        return UserResource::make(User::first());
    }

    // UserResource::collection(User::paginate()) — StaticCall resolver (paginated collection)
    public function paginated(): AnonymousResourceCollection
    {
        return UserResource::collection(User::paginate());
    }

    // UserResource::collection(User::all()) — StaticCall resolver (collection)
    public function collection(): AnonymousResourceCollection
    {
        return UserResource::collection(User::all());
    }
}
