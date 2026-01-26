# Kuantan188 Event Ticketing Platform - AI Coding Guidelines

## Architecture Overview

This is a **full-stack event ticketing system** with a subdomain architecture:
- **Backend**: Laravel 12 API (`backend/`) → `admin.tfcmockup.com`
- **Frontend**: React + TypeScript SPA (`frontend/`) → `tickets.tfcmockup.com`
- **WordPress**: Main site (not in this repo) → root domain

### Key Integration Pattern
- Frontend consumes public API routes (`/api/public/*`) without authentication
- Backend uses Laravel Sanctum for admin authentication (`auth:sanctum` middleware)
- CORS is pre-configured for subdomains (see `backend/config/cors.php`)

## Development Workflows

### Backend Development (Laravel)
```bash
cd backend
composer install
php artisan migrate --seed  # Seeds: CountrySeeder, EventSeeder, TicketSeeder
php artisan serve  # Runs on http://127.0.0.1:8000
```

### Frontend Development (React)
```bash
cd frontend
npm install
npm start  # Runs on http://localhost:3002
```

**Important**: Frontend proxy is configured for `http://localhost:8000` in `package.json`

### Database Seeding Pattern
Always seed in order: `CountrySeeder` → `EventSeeder` → `TicketSeeder` → `AdminUserSeeder`

## Critical Conventions

### API Structure
- **Public routes** (no auth): `/api/public/*` - Used by frontend for events, bookings, reviews
- **Protected routes**: `/api/*` with `auth:sanctum` - Admin dashboard only
- See `backend/routes/api.php` for complete route map

### Booking Flow
Bookings use a **simplified schema** with flattened fields (not normalized):
- `booking_reference` format: `KB{YYYYMMDD}{4-digit-random}` (e.g., `KB202512150001`)
- Required fields: `event_id`, `event_title`, `customer_name`, `email`, `mobile_phone`, `country`, `quantity`, `total_amount`
- Payment methods: `cash_on_delivery`, `bank_transfer`, `online_payment`
- See `backend/app/Http/Controllers/PublicBookingController.php` for validation rules

### Frontend API Configuration
API base URL is environment-aware via `frontend/src/config/api.ts`:
```typescript
// Production: https://admin.tfcmockup.com/api
// Development: http://127.0.0.1:8000/api
```

Use centralized `apiClient` from `frontend/src/services/api.ts` - already configured with interceptors

### Component Structure (Frontend)
- **Pages**: `src/pages/` - Route-level components (Home, Events, EventDetail, etc.)
- **Layout**: `src/components/layout/` - Header, Footer, Layout wrapper
- **Modals**: `src/components/modals/` - ReservationModal, TicketBookingModal (multi-step flows)
- **Shared**: `src/components/` - Reusable UI components

Use React Bootstrap (`react-bootstrap`) for UI components, not raw Bootstrap classes.

## Deployment (GitHub Actions)

### Automatic Deployment Triggers
- **Backend**: Pushes to `backend/**` → deploys to `admin.tfcmockup.com`
- **Frontend**: Pushes to `frontend/**` → builds React app, deploys to `tickets.tfcmockup.com`

### Laravel Production Commands (in deploy workflow)
```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
# Permissions: 755 for storage/bootstrap/cache, www-data owner
```

**Never commit** `.env` files. Use `.env.example` as template and GitHub Secrets for credentials.

## Data Models & Relationships

### Core Models (`backend/app/Models/`)
```
Event → hasMany → Ticket
Event → hasMany → Booking

Country → hasMany → Ticket
Country → hasMany → Booking

Ticket → belongsTo → Event
Ticket → belongsTo → Country
Ticket → hasMany → Booking

Booking → belongsTo → Event (stores event_title for denormalization)
```

### Event Business Logic
- `Event::isBookingOpen()` checks `is_active`, `booking_start_date`, `booking_end_date`
- Events include `ticket_pricing` summary with country-specific prices

## Testing & Debugging

### API Test Endpoints
- `GET /api/public/bookings/test` - Health check, returns columns
- `GET /api/public/events/debug` - Event data inspection
- Frontend test pages: `/api-test`, `/tickets-test`

### Log Files
- Laravel: `backend/storage/logs/laravel.log`
- React dev server: Terminal output with hot reload

## Common Pitfalls

1. **CORS Issues**: If frontend can't reach API, verify origin in `backend/config/cors.php`
2. **Missing Storage Directories**: Run `mkdir -p backend/storage/framework/{cache,sessions,views}` before deploy
3. **Booking Reference Conflicts**: `booking_reference` must be unique (indexed in DB)
4. **Frontend Build Paths**: Production build goes to `frontend/build/`, not `public/`

## Key Files to Reference

- Backend routes: `backend/routes/api.php`
- Frontend routing: `frontend/src/App.tsx`
- API service layer: `frontend/src/services/api.ts`
- Public booking controller: `backend/app/Http/Controllers/PublicBookingController.php`
- Deployment configs: `.github/workflows/deploy-{backend|frontend}.yml`
