# Backend Developer Intern — Take-Home Assessment

**Stack:** Laravel 10 · PHP 8.1 · Pest · `spatie/laravel-data` v4 · `spatie/laravel-query-builder` v5.8 · `spatie/laravel-permission` v6

**Time budget:** ~2–3 focused hours. We are evaluating *whether you can follow our conventions cleanly*, not how much you build. A small, tidy, idiomatic slice beats a large, messy one.

**Delivery Due:** 01/07/2026 at 14:00 HRS (EAT)

> Build this in a **fresh Laravel project** of your own. Set up the listed packages yourself. The point is to see whether you can structure a clean CRUD feature out of the patterns described below.

---

## 1. The scenario

You are building a small slice of an **events platform**. Each *event* sells *tickets* in named **ticket tiers** (e.g. "Early Bird", "VIP", "General Admission").

You will build the **CRUD API slice** for ticket tiers: model, migration, the full CRUD endpoint, plus one custom `publish` action. No frontend, no payments, no PDF — just a clean, idiomatic API resource.

The whole point is the *structure*: we want to see you wire a Laravel Data class, an Action class, a Query Builder index, an API Resource, and a policy into one coherent feature, following the patterns a tidy Laravel dev would.

---

## 2. The concepts you must demonstrate

These are the patterns we live by. Your submission is judged on getting each one right and making them work *together*.

| Concept | What we expect to see |
| --- | --- |
| **Laravel Data class** (input + validation) | A `CreateTicketTierData` and `UpdateTicketTierData`. Validation declared in a `rules(ValidationContext $context)` method. Use `Optional` for non-required fields. |
| **Action class** | One `Create`, one `Update`, one `Delete`, and one `Publish` action. Each has exactly **one public method `execute(...)`**; every other method is `private`. The action owns the write logic and returns the model (or `void`). Controllers must not contain write logic. |
| **API Resource** (`toArray` envelope) | A `TicketTierResource`. Always include `id`; expose every other field via `whenHas`; expose relations via `whenLoaded`. No business logic beyond shaping output. |
| **Spatie Query Builder** (index) | `QueryBuilder::for(...)` with `allowedFilters`, `allowedSorts`, `allowedIncludes`, a default `latest()` ordering, and pagination via a `per_page` query param with a sane default. |
| **Controller conventions** | An `--api` resource controller (no `create`/`edit`). Call `$this->authorize(...)` first. Wrap every write in a `DB::beginTransaction()` / `try` / `catch` block — re-throw `ValidationException`, otherwise log the exception and throw a single generic, translated message. Return a consistent response envelope (see below). |
| **Consistent response envelope** | Mutating endpoints return a uniform JSON envelope carrying a translated `message` and the resource payload — define one small wrapper Resource and use it everywhere, rather than ad-hoc `response()->json([...])`. |
| **Authorization** | A `TicketTierPolicy` with `viewAny`/`view`/`create`/`update`/`delete` backed by `spatie/laravel-permission` (`$user->hasPermissionTo('...')`). State any permission-name assumption you make. |
| **Routing** | `Route::apiResource(...)`, plus one non-resourceful route for the `publish` action. |
| **Pest feature tests** | `tests/Feature` Pest tests with a `beforeEach` that bootstraps a user + permissions. Assert HTTP status, DB state, **and** response payload. |
| **Localization** | Every user-facing string wrapped in `__()`. |

---

## 3. Scope — exactly what to implement

### 3.1 Migration + model

Create `ticket_tiers`:

- `id`
- `event_id` (FK)
- `name` (string)
- `price` (decimal) — money, stored in currency units
- `quantity` (unsigned int) — total tickets available in this tier
- `sales_channels` (json, **nullable**) — `NULL` = sold on all channels; a JSON array (e.g. `["web", "box_office"]`) = only those channels
- `is_published` (boolean, default false)
- `is_active` (boolean, default true)
- timestamps + soft deletes

Index: composite `(event_id, is_active)`.

`TicketTier` model:

- Cast `sales_channels` to array; cast `price` appropriately.
- Query scopes: `forEvent($eventId)`, `active()`, `availableOnChannel($channel)` (matches `sales_channels IS NULL` **OR** the JSON array contains `$channel`).

### 3.2 The CRUD endpoint

An `apiResource` (`index`, `store`, `show`, `update`, `destroy`) **plus** a `publish` action route. Build it with the full convention stack from Section 2:

- `index` via Query Builder — filterable by `event_id` and by `channel` (the latter via an `AllowedFilter::callback` using `availableOnChannel`), sortable by `name`/`price`/`created_at`, paginated.
- `store` / `update` via Data classes + Action classes inside a DB transaction, returning the response envelope.
- `destroy` is a soft delete.
- `publish` flips `is_published` to `true` — single-action style.

**Validation rules that matter (put them in the Data class):**

- `name` must be unique **per event** (not globally — the same name may exist for two different events).
- `event_id` must reference an existing event.
- `price` ≥ 0; `quantity` ≥ 1.
- `sales_channels` nullable; when present, an array whose values are all within a fixed allowed set.

### 3.3 Tests (Pest, `tests/Feature`)

Cover at minimum:

1. `store` creates a tier — assert status, DB row, and response payload.
2. `name` uniqueness is enforced per event, but the same name is allowed across two different events.
3. `availableOnChannel` returns all-channel (`NULL`) + matching-channel tiers and excludes a tier restricted to a different channel.
4. `publish` flips `is_published` to `true`.
5. `destroy` soft-deletes the tier (row remains with `deleted_at`, and is excluded from the index).

---

## 4. Ground rules

- **Follow the conventions** — that's the whole exercise. We are reading how the Data class, Action, Resource, Query Builder, and policy hand off to each other.
- **No frontend, no payments.** `sales_channels` and `price` are simple stored values here.
- Keep `git` history clean and incremental — we read your commits.
- If a requirement is ambiguous, make a reasonable assumption, **state it in your README**, and proceed.
- Run the suite before submitting: `php artisan test`.

---

## 5. How we score (100 pts)

| Area | Pts | What earns the marks |
| --- | --- | --- |
| **Data classes & validation** | 25 | Correct `spatie/laravel-data` v4 usage; `Optional`; per-event unique rule done right; sensible `price`/`quantity`/`sales_channels` rules. |
| **Action classes** | 15 | Single `execute`, private helpers, write logic lives here — not in the controller. |
| **Controller & envelope** | 15 | `--api`, authorize-first, transactions + correct exception handling, consistent response envelope. |
| **Query Builder index** | 15 | Proper `allowedFilters`/`allowedSorts`/`allowedIncludes`, pagination + `latest()`, the `availableOnChannel` filter. |
| **Resource** | 10 | `id` + `whenHas` + `whenLoaded`, no leakage of internals. |
| **Tests** | 15 | Meaningful assertions on status, DB, and payload; the five scenarios covered and passing. |
| **Conventions & polish** | 5 | `__()` everywhere, sensible exception logging, routing placement, policy, clean commits, README with assumptions. |

**Automatic red flags:** business logic in the controller instead of an Action; ad-hoc `response()->json` instead of a consistent envelope; missing transactions on writes; untranslated user-facing strings; global-instead-of-per-event uniqueness.

---

## 6. Deliverable

A git repo (or zip) containing your migration, model, Data classes, Action classes, controller, Resource, policy, routes, and tests — plus a short `README` listing your assumptions, how to run the tests, and a Postman collection to test the API. Good luck.

---

## 7. Note

We encourage use of AI coding agents like Claude and Codex — we believe that's the future of software development and we expect you to use them on this project. Nevertheless, we expect you to be able to defend the architectural choices you made and explain why specific lines of code or decisions came to be.
