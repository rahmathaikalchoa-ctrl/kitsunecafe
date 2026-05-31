# CLAUDE.md — [Kitsune Animal Cafe]

> Catatan penting: Sebaiknya tulis isi CLAUDE.md dalam Bahasa Inggris.
> Claude Code memproses instruksi Bahasa Inggris lebih optimal.
> Template ini ditulis dalam Bahasa Indonesia untuk kemudahan pemahaman.

---

## 1. Project Overview

- Name : [Kitsune Animal Cafe]
- Description : [A web app where customers browse the cafe's food & drink menu, place orders, and read profiles/descriptions of the resident animals so they can get to know each one before visiting.]
- Goal : [Make ordering effortless and help customers connect with the animals at the cafe through rich, friendly animal profiles.]
- Target Users: [Cafe customers/visitors (primary) and staff/admins who manage the menu, animals, and orders (secondary).]
- Version : [v0.1.0]
- Status : [Active development]

---

## 2. Tech Stack

- Language : [PHP]
- Framework : [Laravel]
- Frontend : [Livewire 3 + Blade (TALL stack), Alpine.js for small client-side interactivity]
- Styling : [Tailwind CSS]
- UI Library : [Flux UI]
- Database : [MySQL]
- ORM : [Eloquent]
- Auth : [Laravel's official Livewire starter kit (built-in auth scaffolding). Use Sanctum only if a token/SPA API is added later. Do not use NextAuth / Better Auth / Passport.js — those are Node tools and don't apply here. (Note: "Laravel Passport" ≠ "Passport.js".)]
- State Management: [Server-side via Livewire component properties + session (cart). Alpine.js for ephemeral UI state.]
- Data Fetching : [Eloquent inside Livewire components/controllers. For any JSON API, use API Resources. No SWR/React Query.]
- Package Manager : [Composer (PHP deps) + npm (frontend assets)]
- Deployment : [Laravel Cloud / Laravel Forge + VPS]

---

## 3. Commands

# Setup

composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed

# Development

composer run dev # runs serve + queue + vite concurrently (Laravel 12 default)
php artisan serve # app server only
npm run dev # Vite dev server (HMR) only
npm run build # build assets for production

# Code generation (Artisan)

php artisan make:model Animal -mfc # model + migration + factory + controller
php artisan make:livewire MenuBrowser # Livewire component + blade view
php artisan make:request StoreOrderRequest
php artisan make:enum OrderStatus

# Database

php artisan migrate # run pending migrations
php artisan migrate:fresh --seed # DROP all tables, re-migrate, seed (LOCAL ONLY)
php artisan db:seed # seed data only

# Quality

./vendor/bin/pint # format code (Laravel Pint) — run before every commit
php artisan test # run all tests (Pest)
./vendor/bin/pest --filter=Menu # run a subset

# Production caching

php artisan optimize # config + route + view cache in one command

> Package manager rule: use Composer for PHP and npm for assets.
> Never mix in yarn/pnpm/bun for this project unless the team agrees.

---

## 4. Project Structure

Architecture: [Laravel MVC + Livewire, with business logic extracted into Services/Actions]

```
kitsune-animal-cafe/
  app/
    Models/            # Eloquent models: Animal, MenuItem, Category, Order, OrderItem
    Http/
      Controllers/     # Thin HTTP controllers
      Requests/        # Form Request validation classes
      Middleware/      # Custom middleware
    Livewire/          # Interactive components: MenuBrowser, Cart, AnimalGallery
    Services/          # Business logic (e.g. OrderService, CartService)
    Policies/          # Authorization policies
    Enums/             # PHP enums: OrderStatus, AnimalSpecies
  resources/
    views/
      layouts/         # App layouts
      components/      # Blade (presentational) components
      livewire/        # Livewire component views
      pages/           # Full-page Blade views
    css/               # app.css (Tailwind entry)
    js/                # app.js (Alpine + Vite entry)
  routes/
    web.php            # Web routes
    console.php
  database/
    migrations/        # Schema migrations
    seeders/           # Data seeders
    factories/         # Model factories
  public/              # Web root + compiled assets + image uploads
  tests/
    Feature/
    Unit/
  config/              # Config files
```

Aturan penempatan file:

- New presentational UI → resources/views/components/
- New interactive UI → app/Livewire/ (+ view in resources/views/livewire/)
- Business logic → app/Services/ (keep controllers and Livewire components thin)
- Fixed value sets → app/Enums/
- Validation → app/Http/Requests/
- Do not create new top-level folders without confirmation.

---

## 5. Naming Conventions

```
# Files & Classes (Laravel conventions)
- Model              : PascalCase singular   -> Animal, MenuItem, Order
- Controller         : PascalCase + Controller -> MenuController
- Livewire class     : PascalCase            -> MenuBrowser
- Livewire view      : kebab-case            -> menu-browser.blade.php
- Blade component    : kebab-case            -> animal-card.blade.php
- Form Request       : PascalCase + verb     -> StoreOrderRequest
- Enum               : PascalCase            -> OrderStatus
- Migration          : snake_case timestamped -> create_menu_items_table
- Test file          : PascalCase + Test     -> OrderTest.php

# Database
- Table              : plural snake_case     -> menu_items, animals, orders
- Column             : snake_case            -> is_available, price_cents
- Pivot table        : singular_singular alpha order -> menu_item_order
- Foreign key        : {model}_id            -> category_id, order_id

# In Code
- Variable           : camelCase             -> $menuItem, $isAvailable
- Method             : camelCase             -> getAvailableItems()
- Constant           : UPPER_SNAKE           -> MAX_CART_ITEMS
- Route name         : dot.notation          -> menu.index, animals.show
- Route URL          : kebab-case            -> /menu, /animals/{animal}

# Git Branch
- Feature : feat/[name]      Bug fix : fix/[name]
- Hotfix  : hotfix/[name]    Refactor: refactor/[name]
```

---

## 6. Code Conventions

```
# Approach
- Follow PSR-12. Format every file with Laravel Pint before committing.
- Apply DRY and SOLID. Extract repeated logic into a method or Service.
- Write readable code over clever one-liners.

# PHP / Laravel
- Add declare(strict_types=1); at the top of every PHP class file.
- Always type-hint parameters AND return types. Avoid `mixed` where possible.
- Prefer Eloquent over raw SQL. Use the query builder only when Eloquent is awkward.
- Validate ALL input via Form Request classes — never trust raw request data.
- Keep controllers and Livewire actions thin; push logic into Services/Actions.
- Use route model binding instead of manual findOrFail where possible.
- Use Enums for fixed sets (order status, species).
- Store money as integer cents (price_cents). NEVER use floats for money.

# Error Handling
- Throw exceptions for exceptional flow; let the Laravel handler render them.
- Wrap external/third-party calls (payment, email) in try-catch and log failures.
- Never expose stack traces in production (APP_DEBUG=false).
```

---

## 7. Component Rules

```
# Blade vs Livewire
- Blade component  : pure, stateless presentational UI (animal-card, badge).
- Livewire component: interactive/stateful UI (cart, menu filter, checkout).
- Use Alpine.js for tiny client-only toggles (dropdown open/close) — do not pay
  for a Livewire round-trip just to flip a visual state.

# Order inside a Livewire class
1. Public properties
2. Lifecycle hooks (mount, hydrate, updated...)
3. Computed properties (#[Computed])
4. Actions / public methods
5. Validation rules() / #[Validate]
6. render()

# Props (Blade components)
- Always declare typed props in the @props or constructor.
- Provide defaults for optional props.

# Forms
- Use Flux UI components for inputs, buttons, modals, and selects.
- Validate Livewire forms with rules()/#[Validate]; show inline errors.

# Small components
- Extract to its own file when reused in more than one place.
```

---

## 8. Styling Rules

```
# Approach
- Use Tailwind CSS utility classes directly in Blade.
- No inline styles except for truly dynamic values.
- Never use !important.

# Tailwind
- Use Blade's @class directive for conditional classes (not clsx).
- Extract a Blade component when the same class set repeats.
- Class order: layout > spacing > sizing > color > typography > state.

# Responsive
- Mobile-first. Breakpoints: sm 640 / md 768 / lg 1024 / xl 1280.

# Dark Mode
- Use the Tailwind `dark:` prefix. Test every new component in dark mode.

# Design Tokens
- Define brand colors/spacing in the Tailwind config / CSS variables.
- Do not hardcode hex colors in markup.
```

---

## 9. API & Data Fetching Rules

```
# This is a server-rendered (Livewire) app
- Most data access happens in Livewire components / controllers via Eloquent.
- Do NOT add a client-side fetching library; let the server render the data.

# If a JSON API is added
- Shape responses with API Resources (JsonResource).
- Consistent envelope: { success: bool, data: T|null, message: string }.
- Return correct HTTP status codes (200, 201, 400, 401, 403, 404, 422, 500).
- Never expose internal error details in production.

# Query rules
- Always eager-load relationships to avoid N+1 (->with(...), ->load(...)).
- Paginate long lists (menu, orders) — never load unbounded result sets.
- Put reusable query logic in Eloquent scopes or a Service, not in Blade.

# Environment
- All URLs, keys, and secrets come from environment variables. Never hardcode.
```

---

## 10. State Management Rules

```
# Hierarchy (simplest first)
1. Livewire component property : state local to one component.
2. Session                     : cross-page state like the cart.
3. Database                    : state that must persist per user/order.

# Cart
- Store the cart in the session (guest) and/or DB (logged-in user).
- Do not store derived totals — compute them from line items.

# Alpine.js
- Use only for ephemeral client UI state (menus, tabs, modals open state).

# Avoid
- Do not duplicate data that can be derived from the database.
- Do not push frequently changing data into config or shared singletons.
```

---

## 11. Performance Rules

```
# Database
- Eliminate N+1: eager-load with ->with() / ->load() everywhere.
- Add DB indexes for columns used in WHERE / ORDER BY / foreign keys.
- Paginate listings.

# Caching
- Cache rarely-changing reads (menu, animal list) with Cache::remember().
- In production run `php artisan optimize` (config/route/view cache).
- Ensure OPcache is enabled on the server.

# Images
- Optimize uploaded images; serve responsive sizes.
- Add loading="lazy" to non-critical images.
- Consider Spatie Media Library / Glide for conversions and thumbnails.

# Background work
- Move slow tasks (order confirmation emails, receipts) to queued jobs.

# Frontend
- Let Vite bundle assets; import only what's used.
- Keep Alpine usage minimal to limit shipped JS.
```

---

## 12. Git Rules

After Claude Code finishes a change, commit it before moving to the next task,
so old vs new code can be compared and reverted if needed.

```
# Commit message format
feat     : new feature
fix      : bug fix
refactor : code refactor (no behavior change)
style    : formatting / Pint
docs     : documentation
test     : tests
chore    : config / tooling

# Examples
feat: add cart and checkout flow with session persistence
fix: prevent ordering a menu item that is out of stock
refactor: extract order total calculation into OrderService

# Rules
- Never commit .env or any file containing secrets.
- One commit = one focused change. Don't bundle unrelated changes.
```

---

## 13. Features

```
# In progress — do not change without confirmation
- [ ] [Menu listing page — food & drinks grouped by category]
- [ ] [Animal profiles page with descriptions]
- [ ] [Cart & checkout / order placement]
- [ ] [Customer authentication (Laravel Livewire starter kit)]

# Planned
- [ ] [Admin panel: manage menu, animals, and orders]
- [ ] [Table / animal-session reservation]
- [ ] [Order status tracking for customers]
```

---

## 14. Testing

```
# Approach
- Framework : Pest (runs on PHPUnit). Run with `php artisan test`.
- Feature tests for routes and Livewire components (Livewire::test()).
- Unit tests for Services and helpers.
- Use model factories + RefreshDatabase trait.

# What to test
- Order/cart business logic (totals, stock checks).
- Auth flows (register, login, protected routes).
- Critical Livewire components (MenuBrowser, Cart).

# What not to test
- Trivial presentational Blade components.
- Framework/third-party internals.

# Conventions
- One test file per class under test.
- Descriptive names: it('rejects an unavailable item when adding to cart').
- AAA pattern: Arrange, Act, Assert.

# Coverage
- Minimum coverage : [70]% — priority: business logic > controllers > UI.
```

---

## 15. Do Not

Jika instruksi atau prompt kamu ambigu, TANYA DULU sebelum mulai coding.
Jangan berasumsi dan langsung mengerjakan tanpa konfirmasi.

```
# Structure & files
- Do not create, delete, or move files/folders without confirmation.
- Do not change the existing folder structure.

# Code
- Do not use raw SQL with unescaped user input (SQL injection risk).
- Do not use floats for money — use integer cents.
- Do not put business logic inside Blade views.
- Do not disable CSRF protection.
- Do not bypass $fillable / mass-assignment protection.
- Do not install Composer or npm packages without confirmation.
- Do not remove or change working features without explicit instruction.

# Database
- Do not run migrate:fresh / destructive commands against production.
- Do not edit a migration that has already run on a shared/prod DB — create a new one.
- Do not create migrations without confirmation.

# Security
- Do not commit .env or expose any secret/API key to the client.
- Do not expose DATABASE credentials or detailed errors in production.
- Do not skip validation or authorization (Policies/Gates) on protected actions.
```

---

## 16. Environment Variables

```
# Setup
- Copy .env.example to .env for local development.
- Run `php artisan key:generate`.
- Never commit .env to the repository.

# Core (server-only)
APP_NAME="Kitsune Animal Cafe"
APP_ENV=local            # local | production
APP_KEY=                 # generated by key:generate
APP_DEBUG=true           # MUST be false in production
APP_URL=http://localhost

# Database (server-only)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kitsune_animal_cafe
DB_USERNAME=             # server only
DB_PASSWORD=             # server only

# Sessions / Cache / Queue
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# Mail (order confirmations) — server only
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_USERNAME=
MAIL_PASSWORD=           # server only

# Frontend-exposed (only VITE_ prefixed vars reach the browser)
VITE_APP_NAME="${APP_NAME}"
```

---

_Template ini adalah titik awal. Semakin detail kamu mengisi setiap bagian sesuai kondisi project kamu, semakin baik hasil yang akan diberikan Claude Code. Update CLAUDE.md secara berkala seiring project kamu berkembang._
