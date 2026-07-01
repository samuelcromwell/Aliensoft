<?php

use App\Models\Event;
use App\Models\TicketTier;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $permissions = [
        'ticket-tiers.view-any',
        'ticket-tiers.view',
        'ticket-tiers.create',
        'ticket-tiers.update',
        'ticket-tiers.delete',
    ];

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission);
    }

    $this->user = User::factory()->create();
    $this->user->givePermissionTo($permissions);
    $this->actingAs($this->user);
});

it('creates a ticket tier', function (): void {
    $event = Event::factory()->create();

    $response = $this->postJson('/api/ticket-tiers', [
        'event_id' => $event->id,
        'name' => 'Early Bird',
        'price' => 49.99,
        'quantity' => 120,
        'sales_channels' => ['web', 'mobile'],
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('message', __('Ticket tier created successfully.'))
        ->assertJsonPath('data.event_id', $event->id)
        ->assertJsonPath('data.name', 'Early Bird')
        ->assertJsonPath('data.price', '49.99')
        ->assertJsonPath('data.quantity', 120)
        ->assertJsonPath('data.sales_channels', ['web', 'mobile'])
        ->assertJsonPath('data.is_published', false)
        ->assertJsonPath('data.is_active', true);

    $this->assertDatabaseHas('ticket_tiers', [
        'event_id' => $event->id,
        'name' => 'Early Bird',
        'quantity' => 120,
        'is_published' => false,
        'is_active' => true,
    ]);

    expect(TicketTier::first()->sales_channels)->toBe(['web', 'mobile']);
});

it('enforces name uniqueness per event and allows the same name across events', function (): void {
    $firstEvent = Event::factory()->create();
    $secondEvent = Event::factory()->create();

    TicketTier::factory()->for($firstEvent)->create([
        'name' => 'VIP',
    ]);

    $this->postJson('/api/ticket-tiers', [
        'event_id' => $firstEvent->id,
        'name' => 'VIP',
        'price' => 100,
        'quantity' => 10,
    ])->assertUnprocessable()->assertJsonValidationErrors('name');

    $this->postJson('/api/ticket-tiers', [
        'event_id' => $secondEvent->id,
        'name' => 'VIP',
        'price' => 100,
        'quantity' => 10,
    ])->assertCreated()->assertJsonPath('data.name', 'VIP');

    $this->assertDatabaseHas('ticket_tiers', [
        'event_id' => $secondEvent->id,
        'name' => 'VIP',
    ]);
});

it('filters ticket tiers available on a sales channel', function (): void {
    $event = Event::factory()->create();

    TicketTier::factory()->for($event)->create([
        'name' => 'All Channels',
        'sales_channels' => null,
    ]);

    TicketTier::factory()->for($event)->create([
        'name' => 'Web Only',
        'sales_channels' => ['web'],
    ]);

    TicketTier::factory()->for($event)->create([
        'name' => 'Box Office Only',
        'sales_channels' => ['box_office'],
    ]);

    $response = $this->getJson('/api/ticket-tiers?filter[channel]=web&sort=name');

    $response->assertOk();

    $names = collect($response->json('data'))->pluck('name');

    expect($names)
        ->toContain('All Channels')
        ->toContain('Web Only')
        ->not->toContain('Box Office Only');
});

it('publishes a ticket tier', function (): void {
    $ticketTier = TicketTier::factory()->create([
        'is_published' => false,
    ]);

    $this->postJson("/api/ticket-tiers/{$ticketTier->id}/publish")
        ->assertOk()
        ->assertJsonPath('message', __('Ticket tier published successfully.'))
        ->assertJsonPath('data.id', $ticketTier->id)
        ->assertJsonPath('data.is_published', true);

    $this->assertDatabaseHas('ticket_tiers', [
        'id' => $ticketTier->id,
        'is_published' => true,
    ]);
});

it('soft deletes a ticket tier and excludes it from the index', function (): void {
    $ticketTier = TicketTier::factory()->create([
        'name' => 'Retired',
    ]);

    TicketTier::factory()->create([
        'name' => 'Visible',
    ]);

    $this->deleteJson("/api/ticket-tiers/{$ticketTier->id}")
        ->assertOk()
        ->assertJsonPath('message', __('Ticket tier deleted successfully.'))
        ->assertJsonPath('data.id', $ticketTier->id);

    $this->assertSoftDeleted('ticket_tiers', [
        'id' => $ticketTier->id,
    ]);

    $response = $this->getJson('/api/ticket-tiers?sort=name');

    $response->assertOk();

    $names = collect($response->json('data'))->pluck('name');

    expect($names)
        ->toContain('Visible')
        ->not->toContain('Retired');
});
