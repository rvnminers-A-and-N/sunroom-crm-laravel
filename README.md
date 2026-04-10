# Sunroom CRM — Laravel

A full-stack CRM built on the **TALL stack** (Tailwind CSS, Alpine.js, Livewire, Laravel) with a paired REST API. This is one of four sibling implementations of the same Sunroom CRM product:

| Repo | Stack |
|------|-------|
| [`sunroom-crm-dotnet`](https://github.com/rvnminers-A-and-N/sunroom-crm-dotnet) | ASP.NET Core 9 REST API + Entity Framework Core |
| [`sunroom-crm-angular`](https://github.com/rvnminers-A-and-N/sunroom-crm-angular) | Angular 20 SPA |
| [`sunroom-crm-react`](https://github.com/rvnminers-A-and-N/sunroom-crm-react) | React 19 + Vite SPA |
| [`sunroom-crm-vue`](https://github.com/rvnminers-A-and-N/sunroom-crm-vue) | Vue 3 + Vite SPA |
| **`sunroom-crm-laravel`** | **Laravel 12 + Livewire 3 (TALL stack) — backend AND frontend** |

This Laravel build is unique: the same repo serves the **server-rendered TALL frontend** *and* exposes a **Sanctum-authenticated REST API** that the Angular / React / Vue SPAs can use as a drop-in replacement for the .NET backend.

---

## Features

- **Dashboard** — stat cards, pipeline value chart (Chart.js), recent activity feed
- **Contacts** — paginated CRUD with search, company/tag filters, tag chips, activity timeline
- **Companies** — CRUD with industry, location, contact and deal counts
- **Deals** — table view + drag-and-drop **Kanban pipeline** (SortableJS) with auto-set close dates for Won/Lost
- **Activities** — type-filtered timeline (Note / Call / Email / Meeting / Task) tied to contacts and deals
- **AI Assistant** — chat-style assistant grounded in the user's CRM data, deal-specific insight generation, optional [Ollama](https://ollama.ai/) integration
- **Settings** — tabbed Profile / Password / Tag manager (with native color picker)
- **Admin User Management** — admin-only CRUD for users with role assignment
- **REST API** — full Sanctum-authenticated REST surface mirroring the .NET API contract so the SPA frontends work with this Laravel backend unchanged

---

## Stack

- **Laravel 12** + **PHP 8.5**
- **Livewire 3** for full-page server-driven UI
- **Alpine.js** for client-side interactivity (modals, drag-and-drop wiring, toasts)
- **Tailwind CSS 4** with the Sunroom brand palette (emerald, coral, brand-orange, gold, cream)
- **PostgreSQL 18** (uses `ilike` for case-insensitive search)
- **Laravel Sanctum** for API token authentication
- **Laravel Breeze** (Livewire flavor) for auth scaffolding
- **Pest** for testing
- **Chart.js** and **SortableJS** via CDN

---

## Local Setup

### Prerequisites

- PHP 8.5+ with `pgsql`, `mbstring`, `xml`, `bcmath`, `curl`, `intl` extensions
- Composer 2.8+
- Node 24+ and npm
- PostgreSQL 18 running locally (or update `.env` to point at any PostgreSQL instance)
- *(Optional)* [Ollama](https://ollama.ai/) running locally for AI features

### Install

```bash
git clone https://github.com/rvnminers-A-and-N/sunroom-crm-laravel.git
cd sunroom-crm-laravel

composer install
npm install

cp .env.example .env
php artisan key:generate
```

### Database

Create the PostgreSQL database, then update `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sunroom_crm
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

Run migrations and seed:

```bash
php artisan migrate:fresh --seed
```

This creates 3 users, 5 companies, 8 contacts, 7 deals, 10 activities, and 6 tags so the app is usable immediately.

### Run

```bash
# Terminal 1 — Laravel
php artisan serve

# Terminal 2 — Vite (Tailwind + JS)
npm run dev
```

Visit [http://localhost:8000](http://localhost:8000) and log in with one of the seeded accounts:

| Email | Password | Role |
|-------|----------|------|
| `austin@sunroomcrm.com` | `password` | Admin |
| `sarah@sunroomcrm.com` | `password` | Manager |
| `jake@sunroomcrm.com` | `password` | User |

### Optional: enable AI features

To turn on the AI Assistant and deal-insight generation, install [Ollama](https://ollama.ai/), pull a model, and set:

```env
OLLAMA_ENABLED=true
OLLAMA_BASE_URL=http://localhost:11434
OLLAMA_MODEL=llama3
```

When `OLLAMA_ENABLED=false` (the default), AI views render gracefully with a "disabled" notice and no requests are made.

---

## REST API

All API routes live under `/api` and return camelCase JSON to match the .NET API contract.

### Authentication

```bash
# Register
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"You","email":"you@example.com","password":"password"}'

# Login (returns a Sanctum token)
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"austin@sunroomcrm.com","password":"password"}'
```

Use the returned token as a Bearer header on all subsequent requests:

```bash
curl http://localhost:8000/api/contacts \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Endpoints

| Method | Path | Description |
|--------|------|-------------|
| `POST` | `/api/auth/register` | Register a new user |
| `POST` | `/api/auth/login` | Issue a Sanctum token |
| `POST` | `/api/auth/logout` | Revoke the current token |
| `GET` | `/api/auth/me` | Current user |
| `GET` | `/api/dashboard` | Dashboard stats |
| `GET\|POST\|PUT\|DELETE` | `/api/contacts` | Contact CRUD |
| `POST` | `/api/contacts/{id}/tags` | Sync tags on a contact |
| `GET\|POST\|PUT\|DELETE` | `/api/companies` | Company CRUD |
| `GET\|POST\|PUT\|DELETE` | `/api/deals` | Deal CRUD |
| `GET` | `/api/deals/pipeline` | Deals grouped by stage |
| `GET\|POST\|PUT\|DELETE` | `/api/activities` | Activity CRUD |
| `GET\|POST\|PUT\|DELETE` | `/api/tags` | Tag CRUD |
| `POST` | `/api/ai/summarize` | Summarize a block of text |
| `POST` | `/api/ai/deal-insights/{dealId}` | Generate insights for a deal |
| `POST` | `/api/ai/search` | Smart search across CRM data |
| `GET\|POST\|PUT\|DELETE` | `/api/users` | User management (admin only) |

### Using with the SPA frontends

Point any of the sibling SPAs at `http://localhost:8000/api` and they'll work unchanged. Example for the Angular repo:

```ts
// src/environments/environment.ts
export const environment = {
  apiUrl: 'http://localhost:8000/api',
};
```

---

## Project Structure

```
app/
├── Enums/                  # UserRole, DealStage, ActivityType (PascalCase values)
├── Http/
│   ├── Controllers/Api/    # REST API controllers
│   ├── Requests/           # Form request validation
│   └── Resources/          # API Resources (camelCase JSON)
├── Livewire/               # Full-page Livewire components
│   ├── Activities/
│   ├── Admin/              # Admin-only components
│   ├── Companies/
│   ├── Contacts/
│   ├── Deals/
│   └── Settings.php
├── Models/                 # Eloquent models
├── Policies/               # Ownership-based authorization
└── Services/               # OllamaService for AI features
database/
├── factories/              # Realistic fake data
├── migrations/
└── seeders/                # DatabaseSeeder mirrors .NET SeedData exactly
resources/
├── css/
├── js/
└── views/
    ├── components/         # Reusable Blade components
    ├── layouts/
    └── livewire/           # Livewire view templates
routes/
├── api.php                 # REST API
├── auth.php                # Breeze auth routes
└── web.php                 # TALL frontend routes
```

---

## Architecture Notes

- **No repository layer.** Eloquent is used directly inside Livewire components and API controllers — idiomatic Laravel.
- **Integer primary keys** (not UUIDs) so the SPA frontends are interchangeable between this Laravel backend and the .NET backend.
- **PascalCase enum values** match the .NET JSON output exactly (`Lead`, `Qualified`, `Won`, etc.).
- **camelCase API JSON.** API Resources transform `snake_case` columns to `camelCase` for SPA compatibility.
- **Ownership-based authorization** via Laravel Policies — users only see and edit their own contacts, deals, and activities.
- **Flat Livewire component tree.** Each route maps to a single full-page component; modals and forms live inside via Alpine.js + `@entangle`.
- **Centralized flash toast** (`<x-flash-toast>`) mounted once in the layout listens for `session('success' | 'error')`.

---

## Testing

```bash
php artisan test
```

---

## License

MIT
