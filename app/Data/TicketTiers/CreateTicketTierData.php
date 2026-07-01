<?php

namespace App\Data\TicketTiers;

use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class CreateTicketTierData extends Data
{
    public function __construct(
        public int $event_id,
        public string $name,
        public float|int|string $price,
        public int $quantity,
        public Optional|array|null $sales_channels,
        public Optional|bool $is_active,
    ) {
    }

    public static function rules(ValidationContext $context): array
    {
        $eventId = data_get($context->payload, 'event_id');

        return [
            'event_id' => ['required', 'integer', Rule::exists('events', 'id')],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ticket_tiers', 'name')
                    ->where(fn ($query) => $query->where('event_id', $eventId))
                    ->withoutTrashed(),
            ],
            'price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1'],
            'sales_channels' => ['nullable', 'array'],
            'sales_channels.*' => [Rule::in(config('ticket-tiers.sales_channels', []))],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
