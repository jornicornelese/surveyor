<?php

namespace App\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;

class Money implements Arrayable
{
    public function __construct(
        public readonly int $amount,
        public readonly string $currency,
    ) {
        //
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
        ];
    }
}
