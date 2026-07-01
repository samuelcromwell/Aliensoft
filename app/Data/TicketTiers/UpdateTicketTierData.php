<?php

namespace App\Data\TicketTiers;

use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class UpdateTicketTierData extends Data
{
    public function __construct(
        public Optional|int $event_id,
        public Optional|string $name,
        public Optional|float|int|string $price,
        public Optional|int $quantity,
        public Optional|array|null $sales_channels,
        public Optional|bool $is_active,
    ) {}

    public static function rules(ValidationContext $context): array
    {
        $eventId = data_get(
            $context->payload,
            'event_id',
            data_get($context->payload, '_current_event_id')
        );

        $ticketTierId = data_get($context->payload, '_ticket_tier_id');

        return [
            'event_id' => ['sometimes', 'integer', Rule::exists('events', 'id')],
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('ticket_tiers', 'name')
                    ->where(fn ($query) => $query->where('event_id', $eventId))
                    ->ignore($ticketTierId)
                    ->withoutTrashed(),
            ],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'sales_channels' => ['sometimes', 'nullable', 'array'],
            'sales_channels.*' => [Rule::in(config('ticket-tiers.sales_channels', []))],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
