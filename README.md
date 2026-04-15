# Sunroom CRM — Laravel

This is the Laravel full-stack implementation of [Sunroom CRM](https://sunroomcrm.net), built on the **TALL stack** (Tailwind CSS, Alpine.js, Livewire, Laravel) with a paired REST API, backed by PostgreSQL.

## About Sunroom CRM

Sunroom CRM is a multi-frontend platform where the same business requirements are implemented across multiple technology stacks. The project showcases how different frontend ecosystems approach the same real-world problems: authentication, CRUD operations, real-time data visualization, drag-and-drop workflows, role-based access control, and AI-powered features.

Every frontend shares a single .NET 8 REST API and SQL Server database, and this Laravel build additionally serves its own Sanctum-authenticated API that the SPA frontends can use as a drop-in replacement for the .NET backend.

### The Full Stack

| Repository | Technology | Description |
|------------|-----------|-------------|
| [`sunroom-crm-dotnet`](https://github.com/rvnminers-A-and-N/sunroom-crm-dotnet) | .NET 8 / EF Core | Shared REST API with JWT auth, AI endpoints, and Docker support |
| [`sunroom-crm-angular`](https://github.com/rvnminers-A-and-N/sunroom-crm-angular) | Angular 21 | Angular frontend with 100% test coverage |
| [`sunroom-crm-react`](https://github.com/rvnminers-A-and-N/sunroom-crm-react) | React 19 / Vite | React frontend with 100% test coverage |
| [`sunroom-crm-vue`](https://github.com/rvnminers-A-and-N/sunroom-crm-vue) | Vue 3 / Vite | Vue frontend |
| [`sunroom-crm-blazor`](https://github.com/rvnminers-A-and-N/sunroom-crm-blazor) | Blazor / .NET 8 | WebAssembly frontend |
| **`sunroom-crm-laravel`** | **Laravel 13 / Livewire 3** | **Full-stack (backend + frontend) with 100% test coverage** |

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 13 + PHP 8.5 |
| UI | Livewire 3 + Alpine.js |
| Styling | Tailwind CSS 4 |
| Charts | Chart.js 4 (CDN) |
| Drag & Drop | SortableJS (CDN) |
| Database | PostgreSQL 18 |
| Auth | Laravel Sanctum + Breeze |
| Unit / Feature Tests | PEST 4 + pest-plugin-livewire |
| Browser Tests | Laravel Dusk 8 |
| E2E Tests | Playwright 1.59 (Chromium, Firefox, WebKit) |
| CI/CD | GitHub Actions |

## Features

- **Dashboard** — stat cards, pipeline value chart (Chart.js), recent activity feed
- **Contacts** — paginated CRUD with search, company/tag filters, tag chips, activity timeline
- **Companies** — CRUD with industry, location, contact and deal counts
- **Deals** — table view + drag-and-drop Kanban pipeline (SortableJS) with auto-set close dates for Won/Lost
- **Activities** — type-filtered timeline (Note / Call / Email / Meeting / Task) tied to contacts and deals
- **AI Assistant** — chat-style assistant grounded in the user's CRM data, deal-specific insight generation, optional Ollama integration
- **Settings** — tabbed Profile / Password / Tag manager (with native color picker)
- **Admin User Management** — admin-only CRUD for users with role assignment
- **REST API** — full Sanctum-authenticated REST surface mirroring the .NET API contract so the SPA frontends work with this Laravel backend unchanged
- **Responsive Layout** — sidebar navigation with mobile-friendly collapse

## Getting Started

### Prerequisites

- PHP 8.5+ with `pgsql`, `mbstring`, `xml`, `bcmath`, `curl`, `intl` extensions
- Composer 2.8+
- Node.js 24+ and npm 11+
- PostgreSQL 18 running locally
- *(Optional)* [Ollama](https://ollama.ai/) for AI features

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
| `austin@sunroomcrm.net` | `password` | Admin |
| `sarah@sunroomcrm.net` | `password` | Manager |
| `jake@sunroomcrm.net` | `password` | User |

### Optional: enable AI features

Install [Ollama](https://ollama.ai/), pull a model, and set:

```env
OLLAMA_ENABLED=true
OLLAMA_BASE_URL=http://localhost:11434
OLLAMA_MODEL=llama3
```

When `OLLAMA_ENABLED=false` (the default), AI views render gracefully with a "disabled" notice.

## Available Scripts

| Command | Description |
|---------|-------------|
| `php artisan serve` | Start the development server |
| `npm run dev` | Start Vite dev server (Tailwind + JS) |
| `npm run build` | Production build |
| `php artisan test` | Run PEST unit and feature tests |
| `php artisan test --coverage` | Run PEST with coverage report |
| `php artisan dusk` | Run Dusk browser tests |
| `npx playwright test` | Run Playwright E2E tests |
| `./vendor/bin/pint` | Fix code style (Laravel Pint) |

## Testing

### Unit and Feature Tests

502 PEST tests across 61 suites at **100% line coverage**, enforced by the CI pipeline.

```bash
php artisan test --coverage --min=100
```

Tests cover all Eloquent models, Livewire components, API controllers, form requests, policies, middleware, enums, services, and Blade view components.

### Browser Tests (Dusk)

14 Dusk browser tests across 5 suites running in headless Chrome:

- **LoginFlowTest** — authentication, wrong password error, unauthenticated redirect
- **ContactCrudTest** — full create/edit/delete modal flow, inline validation
- **DealPipelineDragTest** — Kanban column rendering, stage transitions, closed_at behavior
- **AdminRouteGuardTest** — 403 for non-admins, admin access, sidebar link visibility
- **NavigationTest** — sidebar navigation between pages, logout flow

### E2E Tests (Playwright)

7 Playwright tests across 3 browsers (Chromium, Firefox, WebKit) — 21 total test runs:

- **auth-guard** — unauthenticated redirect, admin route 403
- **cross-browser-render** — public and authenticated pages render without console errors
- **golden-path** — dashboard, create contact, create deal, pipeline view, logout

### API Compatibility Tests

A Node.js test script (`tests/ApiCompat/api-compat.test.mjs`) registers a user on both the Laravel and .NET APIs, then hits matching endpoints and compares response shapes to verify the two backends are interchangeable.

## CI/CD Pipeline

GitHub Actions runs five jobs on every push and pull request:

| Job | Description |
|-----|-------------|
| **Pint** | Code style check — fails fast before tests run |
| **PEST** | Unit and feature tests on PHP 8.4 and 8.5 with 100% coverage gate |
| **Dusk** | Browser tests in headless Chrome against PostgreSQL |
| **Playwright** | Cross-browser E2E across Chromium, Firefox, and WebKit |
| **API Compat** | Spins up the .NET API via Docker Compose and compares response shapes (gated behind `RUN_API_COMPAT` variable) |

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
  -d '{"email":"austin@sunroomcrm.net","password":"password"}'
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

Point any of the sibling SPAs at `http://localhost:8000/api` and they work unchanged. Example for Angular:

```ts
// src/environments/environment.ts
export const environment = {
  apiUrl: 'http://localhost:8000/api',
};
```

## Architecture

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
tests/
├── Feature/                # 61 PEST test suites
├── Browser/                # 5 Dusk test suites
├── ApiCompat/              # API compatibility test script
playwright/
└── tests/                  # 3 Playwright spec files
```

### Key Patterns

- **No repository layer** — Eloquent is used directly inside Livewire components and API controllers, idiomatic Laravel
- **Integer primary keys** (not UUIDs) so the SPA frontends are interchangeable between this Laravel backend and the .NET backend
- **PascalCase enum values** match the .NET JSON output exactly (`Lead`, `Qualified`, `Won`, etc.)
- **camelCase API JSON** — API Resources transform `snake_case` columns to `camelCase` for SPA compatibility
- **Ownership-based authorization** via Laravel Policies — users only see and edit their own contacts, deals, and activities
- **Flat Livewire component tree** — each route maps to a single full-page component; modals and forms live inside via Alpine.js + `@entangle`
- **Centralized flash toast** (`<x-flash-toast>`) mounted once in the layout listens for `session('success' | 'error')`
- **Three-tier testing** — PEST for unit/feature, Dusk for browser, Playwright for cross-browser E2E
- **API parity testing** — Node.js script compares Laravel and .NET API response shapes to guarantee frontend interchangeability

## License

This project is licensed under the MIT License. See [LICENSE](LICENSE) for details.
