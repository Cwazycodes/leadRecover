<p align="center">
  <strong>LeadRecover</strong> — Turn missed calls into paying customers.
</p>

LeadRecover is a production-ready, multi-tenant SaaS that helps local
businesses (dentists, salons, barbers, physios, clinics, estate agents,
driving instructors…) recover revenue lost to missed phone calls. When a call
goes unanswered, LeadRecover instantly texts the caller back over **SMS or
WhatsApp**, captures the lead, nudges them to book, and tracks everything in a
clean dashboard — with automated 1h / 24h follow-ups.

- **Stack:** Laravel 12 · PHP 8.4 · MySQL · Redis · Tailwind CSS · Alpine.js · Laravel Breeze · Laravel Cashier (Stripe) · Twilio (Voice/SMS/WhatsApp) · Queue workers · Docker
- **Demo login (after seeding):** `demo@leadrecover.test` / `password`
- **Platform admin:** `admin@leadrecover.test` / `password`

---

## Table of contents

1. [Architecture](#architecture)
2. [Project structure](#project-structure)
3. [Data model](#data-model)
4. [Quick start (local)](#quick-start-local)
5. [Running with Docker](#running-with-docker)
6. [Configuration](#configuration-twilio--stripe)
7. [Webhooks](#webhooks)
8. [Routes & API](#routes--api)
9. [Testing](#testing)
10. [Production deployment](#production-deployment)
11. [Security](#security)
12. [Implementation plan](#implementation-plan)

---

## Architecture

**Multi-tenancy (row-level).** The `Business` model is the tenant boundary and
the Stripe billable entity. Every customer-owned record (`Lead`, `Call`,
`Interaction`, `Subscription`, `User`) is keyed by `business_id`.

- `App\Tenancy\Tenancy` — a request-scoped singleton holding the active business.
- `App\Tenancy\TenantScope` — a global Eloquent scope evaluated **at query time**, so reads can never leak across tenants (even when switching tenants inside a queue worker).
- `App\Models\Concerns\BelongsToTenant` — applies the scope and auto-fills `business_id` on create.
- `IdentifyTenant` middleware sets the tenant for authenticated web traffic; `{lead}` route binding is additionally scoped so another tenant's records return **404**.

**Missed-call recovery flow.**

```
Twilio Voice webhook ─▶ TwilioVoiceController ─▶ LeadService::createFromMissedCall()
        │                                              │
        │                                       creates Call + Lead
        │                                              │
        ▼                                       LeadCaptured event
  (no-answer / busy)                                   │
                                  ┌────────────────────┼─────────────────────┐
                                  ▼                    ▼                      ▼
                        TriggerRecoveryFlow   SendNewLeadNotification   (team alerted)
                                  │
        ┌─────────────────────────┼──────────────────────────┐
        ▼                         ▼                            ▼
 SendInitialOutreachJob   SendFollowUpReminderJob (1h, 24h)   MarkLeadStaleJob (72h)
        │                         │                            │
   SMS / WhatsApp           guarded: skips if the lead replied or booked
```

Every job is **self-guarding**: if the lead replies (inbound webhook →
`LeadResponded` → `HaltFollowUps`) or is booked/converted, pending reminders
no-op, so a customer is never pestered. A scheduled `leads:sweep-stale` command
is a safety net if a delayed job is ever lost.

**Layered design:** thin Controllers → Services (`LeadService`, `TwilioService`,
`AnalyticsService`, `MessageComposer`) → Repositories (`LeadRepository`) →
Models. Cross-cutting concerns live in Events/Listeners, Jobs, Notifications,
Mailables, Policies and Middleware.

---

## Project structure

```
app/
├── Console/Commands/      SweepStaleLeads, SendTrialReminders
├── Events/                LeadCaptured, LeadResponded
├── Http/
│   ├── Controllers/       Dashboard, Lead, LeadInteraction, Settings, Billing,
│   │   │                  Booking, Marketing, Notification, Auth/*
│   │   ├── Admin/         DashboardController, CustomerController
│   │   └── Webhooks/      TwilioVoiceController, TwilioMessageController
│   └── Middleware/        IdentifyTenant, EnsureSubscribed,
│                          EnsurePlatformAdmin, VerifyTwilioSignature
├── Jobs/                  SendInitialOutreachJob, SendFollowUpReminderJob,
│                          MarkLeadStaleJob
├── Listeners/             TriggerRecoveryFlow, SendNewLeadNotification,
│                          HaltFollowUps, HandleStripeWebhook
├── Mail/                  Welcome, TrialEnding, PaymentFailed, SubscriptionCancelled
├── Models/                Business, User, Lead, Call, Interaction
├── Notifications/         NewLeadNotification, LeadRepliedNotification
├── Policies/              LeadPolicy, BusinessPolicy
├── Repositories/          LeadRepository (+ contract)
├── Services/              LeadService, TwilioService, AnalyticsService, MessageComposer
└── Tenancy/               Tenancy, TenantScope
config/leadrecover.php     Plans, trial, follow-up cadence, templates, industries
database/{migrations,factories,seeders}
docker/                    nginx + php config, entrypoint
resources/views/           marketing, dashboard, leads, settings, billing, admin,
                           booking, emails, components (Tailwind + Alpine)
routes/                    web.php, api.php, webhooks.php, console.php
tests/                     Feature + Unit (70 tests)
```

---

## Data model

| Table | Purpose | Key columns |
|---|---|---|
| `businesses` | Tenant + Stripe billable | name, slug, twilio_number, forward_to_number, industry, booking_type, sms/whatsapp templates, opening_hours (json), trial_ends_at, stripe_id |
| `users` | Team members | business_id, role (owner/admin/staff), is_platform_admin |
| `leads` | Recoverable opportunity | business_id, phone, name, source, status, booking_date, estimated_value, follow_up_stage, responded_at |
| `calls` | Raw Twilio call records | business_id, lead_id, call_sid, status (no-answer/busy…), occurred_at |
| `interactions` | Unified timeline | business_id, lead_id, channel (sms/whatsapp/voice/note/system), direction, body, provider_sid |
| `subscriptions` / `subscription_items` | Cashier billing (keyed by `business_id`) | stripe_status, stripe_price |

Lead statuses: `new → contacted → booked → converted` (plus `lost`, `stale`).

---

## Quick start (local)

Requires PHP 8.4, Composer and Node 20+. Uses SQLite out of the box.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate

# Demo Stripe price IDs so seeded billing/MRR resolve locally:
#   STRIPE_PRICE_STARTER=price_starter
#   STRIPE_PRICE_GROWTH=price_growth
#   STRIPE_PRICE_PRO=price_pro

php artisan migrate --seed     # creates demo business + sample data
npm run build                  # or `npm run dev` for hot reload
php artisan serve
```

Open <http://localhost:8000> and log in with **demo@leadrecover.test /
password**. For the platform-owner view, log in as **admin@leadrecover.test /
password** (`/admin`).

> Twilio and Stripe are **disabled by default** (`TWILIO_ENABLED=false`).
> Outbound messages are logged instead of sent, so the whole recovery flow can
> be exercised without any third-party credentials.

---

## Starting everything locally (dev)

You need four things running at once. Open four terminal tabs:

**Tab 1 — Laravel server**
```bash
php artisan serve --port=8001
```

**Tab 2 — Vite (frontend assets with hot reload)**
```bash
npm run dev
```

**Tab 3 — Queue worker (processes outbound SMS jobs)**
```bash
php artisan queue:work
```

**Tab 4 — ngrok (public HTTPS tunnel for webhooks)**
```bash
# First time only — log in and use your free static domain:
ngrok config add-authtoken <your-ngrok-token>

# Then start the tunnel using your free static domain:
ngrok http --domain=<your-static-subdomain>.ngrok-free.app 8001
```

> Your free static ngrok domain never changes between restarts. Find it at
> **ngrok dashboard → Domains**. Use it everywhere below.

### Pointing webhooks at your tunnel

**Stripe** — go to Stripe Dashboard → Developers → Webhooks → Add destination:
- URL: `https://<your-static-subdomain>.ngrok-free.app/stripe/webhook`
- Events: `invoice.payment_failed`, `customer.subscription.deleted`, `customer.subscription.trial_will_end`
- Copy the `whsec_...` signing secret into `.env` as `STRIPE_WEBHOOK_SECRET`

**Twilio** — go to Twilio Console → Phone Numbers → your number → Configure:
- Voice webhook (incoming call): `https://<your-static-subdomain>.ngrok-free.app/webhooks/twilio/voice`
- Voice status callback: `https://<your-static-subdomain>.ngrok-free.app/webhooks/twilio/voice/status`
- Messaging webhook (inbound SMS/WhatsApp): `https://<your-static-subdomain>.ngrok-free.app/webhooks/twilio/message`
- Messaging status callback: `https://<your-static-subdomain>.ngrok-free.app/webhooks/twilio/message/status`

---

## Running with Docker

The compose stack runs the app (php-fpm), nginx, a queue worker, the scheduler,
MySQL 8 and Redis 7.

```bash
cp .env.example .env
php artisan key:generate            # or set APP_KEY manually

# In .env, switch to the Docker services:
#   DB_CONNECTION=mysql  DB_HOST=mysql  DB_DATABASE=leadrecover
#   DB_USERNAME=leadrecover  DB_PASSWORD=secret
#   REDIS_HOST=redis  QUEUE_CONNECTION=redis  CACHE_STORE=redis  SESSION_DRIVER=redis

docker compose build
docker compose up -d
docker compose exec app php artisan migrate --seed   # first run only
```

Open <http://localhost:8080>. The `app` container migrates automatically on
boot when `RUN_MIGRATIONS=true`; the seed above populates demo data.

---

## Configuration (Twilio & Stripe)

### Twilio
1. Set `TWILIO_ACCOUNT_SID`, `TWILIO_AUTH_TOKEN`, `TWILIO_SMS_FROM` (and
   `TWILIO_WHATSAPP_FROM`) in `.env`, then `TWILIO_ENABLED=true`.
2. In **Settings → Business profile**, each business sets its **Twilio number**
   (the number that receives calls) and an optional **forward-to** number.
3. Point the number's Twilio webhooks at the app (see below). Inbound signatures
   are verified (`TWILIO_VALIDATE_SIGNATURE=true`).

### Stripe
1. Create three recurring **Prices** (Starter £49, Growth £99, Pro £199) and put
   their IDs in `STRIPE_PRICE_STARTER/GROWTH/PRO`.
2. Set `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`.
3. Add a webhook endpoint `POST {APP_URL}/stripe/webhook` (Cashier verifies the
   signature). Checkout, the customer portal, upgrades/downgrades, cancellation
   and resume are all wired up on the **Billing** page.

---

## Webhooks

| Method & path | Purpose |
|---|---|
| `POST /webhooks/twilio/voice` | Incoming call — returns TwiML (forwards to the business, or captures a missed call) |
| `POST /webhooks/twilio/voice/status` | Dial status callback — `no-answer`/`busy`/`failed` ⇒ create lead + recovery flow |
| `POST /webhooks/twilio/message` | Inbound SMS/WhatsApp — records the reply, marks the lead responded, halts follow-ups |
| `POST /webhooks/twilio/message/status` | Delivery-status callbacks for sent messages |
| `POST /stripe/webhook` | Cashier (billing sync) + lifecycle emails (payment failed, cancelled, trial ending) |

Twilio routes are stateless (no CSRF/session) and protected by
`VerifyTwilioSignature`.

---

## Routes & API

- **Public:** `/` (landing), `/pricing`, `/book/{business}` (white-label booking page)
- **App (auth + tenant + active plan):** `/dashboard`, `/leads`, `/leads/{lead}`, `/settings`, `/billing`
- **Admin (platform owner):** `/admin`, `/admin/customers`
- **JSON API** (`routes/api.php`): `GET /api/v1/health`, `GET /api/v1/pipeline` (session-auth)

Run `php artisan route:list` for the full map.

---

## Testing

70 feature + unit tests cover registration/trial, tenant isolation, the
missed-call recovery flow, inbound messages, follow-up guards, billing
authorization, webhook signature security, analytics and page rendering.

```bash
php artisan test
```

Tests run on in-memory SQLite with the queue/mail faked.

---

## Production deployment

1. **Provision** PHP 8.4 (with `bcmath`, `intl`, `gd`, `zip`, `pdo_mysql`,
   `redis`), MySQL 8 and Redis 7 — or use the Docker image.
2. **Environment:** set `APP_ENV=production`, `APP_DEBUG=false`, a strong
   `APP_KEY`, the DB/Redis/Mail/Twilio/Stripe credentials, and
   `QUEUE_CONNECTION=redis`, `CACHE_STORE=redis`, `SESSION_DRIVER=redis`.
3. **Build & cache:** `composer install --no-dev -o`, `npm ci && npm run build`,
   `php artisan migrate --force`, `php artisan optimize` (config/route/view/event
   cache), `php artisan storage:link`.
4. **Workers:** run a **queue worker** (`php artisan queue:work`) and the
   **scheduler** (`php artisan schedule:work`, or a one-line cron calling
   `schedule:run` every minute). The Docker stack already includes both.
5. **TLS** in front of nginx; point Twilio & Stripe webhooks at the HTTPS URL.

---

## Security

- Tenant isolation via global scope **and** scoped route-model binding (foreign records 404).
- Authorization via `LeadPolicy` / `BusinessPolicy` (owners/admins manage settings & billing).
- Twilio webhook signature verification; Stripe webhook signature verification (Cashier).
- Rate limiting on webhooks (`throttle:webhooks`), the API and the public booking form.
- CSRF protection on all stateful forms; validation on every request; hashed passwords; email verification.

---

## Implementation plan

The repository was built in the following order (see git history):

1. **Scaffold** — Laravel 12 + Breeze (Tailwind/Alpine), Cashier, Twilio SDK, Predis.
2. **Data layer** — tenancy (Business/scope/trait), migrations, models.
3. **Domain services** — LeadService, TwilioService, AnalyticsService, MessageComposer; events, listeners, jobs, notifications, mailables.
4. **HTTP layer** — controllers, middleware, policies, routes (web/api/webhooks).
5. **UI** — Tailwind design system, marketing site, dashboard, leads, settings, billing, admin, booking page.
6. **Data** — factories + a rich demo seeder.
7. **Tests** — 70 feature/unit tests.
8. **DevOps** — Docker stack, scheduled commands, deployment docs.

---

Built with care. Turn missed calls into paying customers. 📞 → 💬 → 📅 → 💷
