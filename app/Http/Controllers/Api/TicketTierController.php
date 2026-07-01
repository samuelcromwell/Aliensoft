<?php

namespace App\Http\Controllers\Api;

use App\Actions\TicketTiers\CreateTicketTierAction;
use App\Actions\TicketTiers\DeleteTicketTierAction;
use App\Actions\TicketTiers\PublishTicketTierAction;
use App\Actions\TicketTiers\UpdateTicketTierAction;
use App\Data\TicketTiers\CreateTicketTierData;
use App\Data\TicketTiers\UpdateTicketTierData;
use App\Http\Controllers\Controller;
use App\Http\Resources\MutationResponseResource;
use App\Http\Resources\TicketTierResource;
use App\Models\TicketTier;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class TicketTierController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', TicketTier::class);

        $ticketTiers = QueryBuilder::for(TicketTier::query()->latest())
            ->allowedFilters([
                AllowedFilter::exact('event_id'),
                AllowedFilter::callback('channel', function ($query, mixed $value): void {
                    $query->availableOnChannel((string) $value);
                }),
            ])
            ->allowedSorts(['name', 'price', 'created_at'])
            ->allowedIncludes(['event'])
            ->paginate($request->integer('per_page', 15))
            ->appends($request->query());

        return TicketTierResource::collection($ticketTiers);
    }

    public function store(Request $request, CreateTicketTierAction $createTicketTierAction)
    {
        $this->authorize('create', TicketTier::class);

        DB::beginTransaction();

        try {
            $data = CreateTicketTierData::validateAndCreate($request->all());
            $ticketTier = $createTicketTierAction->execute($data);

            DB::commit();

            return (new MutationResponseResource(
                __('Ticket tier created successfully.'),
                TicketTierResource::make($ticketTier)
            ))->response()->setStatusCode(Response::HTTP_CREATED);
        } catch (ValidationException $exception) {
            DB::rollBack();

            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();

            $this->throwWriteException($exception, __('Unable to create ticket tier.'));
        }
    }

    public function show(TicketTier $ticketTier)
    {
        $this->authorize('view', $ticketTier);

        return TicketTierResource::make($ticketTier);
    }

    public function update(Request $request, TicketTier $ticketTier, UpdateTicketTierAction $updateTicketTierAction)
    {
        $this->authorize('update', $ticketTier);

        DB::beginTransaction();

        try {
            $data = UpdateTicketTierData::validateAndCreate([
                ...$request->all(),
                '_ticket_tier_id' => $ticketTier->id,
                '_current_event_id' => $ticketTier->event_id,
            ]);

            $ticketTier = $updateTicketTierAction->execute($ticketTier, $data);

            DB::commit();

            return new MutationResponseResource(
                __('Ticket tier updated successfully.'),
                TicketTierResource::make($ticketTier)
            );
        } catch (ValidationException $exception) {
            DB::rollBack();

            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();

            $this->throwWriteException($exception, __('Unable to update ticket tier.'));
        }
    }

    public function destroy(TicketTier $ticketTier, DeleteTicketTierAction $deleteTicketTierAction)
    {
        $this->authorize('delete', $ticketTier);

        DB::beginTransaction();

        try {
            $deleteTicketTierAction->execute($ticketTier);

            DB::commit();

            return new MutationResponseResource(
                __('Ticket tier deleted successfully.'),
                TicketTierResource::make($ticketTier)
            );
        } catch (ValidationException $exception) {
            DB::rollBack();

            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();

            $this->throwWriteException($exception, __('Unable to delete ticket tier.'));
        }
    }

    public function publish(TicketTier $ticketTier, PublishTicketTierAction $publishTicketTierAction)
    {
        $this->authorize('update', $ticketTier);

        DB::beginTransaction();

        try {
            $ticketTier = $publishTicketTierAction->execute($ticketTier);

            DB::commit();

            return new MutationResponseResource(
                __('Ticket tier published successfully.'),
                TicketTierResource::make($ticketTier)
            );
        } catch (ValidationException $exception) {
            DB::rollBack();

            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();

            $this->throwWriteException($exception, __('Unable to publish ticket tier.'));
        }
    }

    private function throwWriteException(Throwable $exception, string $message): never
    {
        Log::error($message, ['exception' => $exception]);

        throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, $message, $exception);
    }
}
