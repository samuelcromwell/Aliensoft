# Ticket Tier API Assessment

Laravel 10 API slice for managing ticket tiers on an events platform.

## Stack

- PHP 8.1+
- Laravel 10
- Pest
- spatie/laravel-data v4
- spatie/laravel-query-builder v5.8
- spatie/laravel-permission v6

## Assumptions

- A minimal `events` table is included only so `ticket_tiers.event_id` can have a real foreign key.
- Ticket-tier permissions use these names:
  - `ticket-tiers.view-any`
  - `ticket-tiers.view`
  - `ticket-tiers.create`
  - `ticket-tiers.update`
  - `ticket-tiers.delete`
- The custom publish action is authorized with the same `ticket-tiers.update` permission.
- Allowed sales channel values are configured in `config/ticket-tiers.php`: `web`, `box_office`, `mobile`, and `partner`.
- `sales_channels = null` means a tier is available on all channels.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

## Tests

```bash
php artisan test
```

## API

Routes are registered in `routes/api.php`.

- `GET /api/ticket-tiers`
- `POST /api/ticket-tiers`
- `GET /api/ticket-tiers/{ticket_tier}`
- `PATCH /api/ticket-tiers/{ticket_tier}`
- `DELETE /api/ticket-tiers/{ticket_tier}`
- `POST /api/ticket-tiers/{ticket_tier}/publish`

The Postman collection is provided at `postman/ticket-tier-api.postman_collection.json`.
