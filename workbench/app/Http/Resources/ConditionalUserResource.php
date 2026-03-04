<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConditionalUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // when() — no default: value becomes optional
            'secret' => $this->when(true, 'secret-value'),

            // when() — with default: union of value and default types
            'role' => $this->when(true, 'admin', 'guest'),

            // when() — closure value (no default): optional closure return type
            'computed' => $this->when(true, fn (): string => 'computed'),

            // unless() — same indices as when()
            'unless_field' => $this->unless(false, 'exposed'),

            // whenLoaded() — 1-arg form: no static type info → mixed optional
            'posts' => $this->whenLoaded('posts'),

            // whenLoaded() — 2-arg form with closure: optional closure return type
            'posts_loaded' => $this->whenLoaded('posts', fn (): string => 'loaded'),

            // whenNotNull() — 1-arg: the value itself becomes optional
            'nullable_field' => $this->whenNotNull('some-value'),

            // whenNotNull() — 2-arg: union of value and default
            'display_name' => $this->whenNotNull('primary', 'fallback'),

            // whenNull() — same indices as whenNotNull()
            'null_field' => $this->whenNull('present', 'absent'),

            // whenCounted() — 1-arg: mixed optional
            'posts_count' => $this->whenCounted('posts'),

            // whenHas() — 1-arg: mixed optional
            'bio' => $this->whenHas('bio'),

            // whenAppended() — 1-arg: mixed optional
            'appended' => $this->whenAppended('full_name'),

            // whenAggregated() — 3-arg (no value arg): mixed optional
            'words_avg' => $this->whenAggregated('posts', 'words', 'avg'),

            // whenAggregated() — 4-arg: optional closure return type
            'words_sum' => $this->whenAggregated('posts', 'words', 'sum', fn (): int => 0),

            // whenPivotLoaded() — 2-arg: optional closure return type
            'expires_at' => $this->whenPivotLoaded('role_user', fn (): string => 'date'),

            // whenPivotLoadedAs() — 3-arg: optional closure return type
            'pivot_field' => $this->whenPivotLoadedAs('subscription', 'role_user', fn (): string => 'value'),

            // whenExistsLoaded() — 1-arg: always bool optional (withExists() casts to bool)
            'posts_exists' => $this->whenExistsLoaded('posts'),

            // whenExistsLoaded() — 2-arg: optional closure return type
            'posts_exists_loaded' => $this->whenExistsLoaded('posts', fn (): string => 'exists'),

            // merge() — value at index 0, no default: optional array
            'merged' => $this->merge(['extra' => 'data']),

            // mergeWhen() — value at index 1, no default: optional array
            'merged_when' => $this->mergeWhen(true, ['conditional' => 'data']),

            // mergeUnless() — same indices as mergeWhen()
            'merged_unless' => $this->mergeUnless(false, ['unless_data' => 'data']),
        ];
    }
}
