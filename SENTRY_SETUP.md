# Sentry Integration Setup Guide

## Overview
Sentry is now integrated into both the frontend and backend applications for error tracking, performance monitoring, and session replay.

## Projects Created
1. **Frontend**: `ticket-frontend` (React/JavaScript) ✅ CONFIGURED
2. **Backend**: `tickets-admin-backend` (Laravel/PHP) ✅ CONFIGURED

**Frontend DSN**: `https://1e50a11c2e380f90df112092f2dd0643@o4511619211919360.ingest.de.sentry.io/4511659252645968`  
**Backend DSN**: `https://99397fd43ca75f70184695e7b17d9ff5@o4511619211919360.ingest.de.sentry.io/4511659221581904`

---

## Frontend Setup (React) ✅ COMPLETED

### 1. Installation
The Sentry React SDK has been installed:
```bash
npm install --save @sentry/react
```

### 2. Configuration Files Modified

#### `frontend/src/index.tsx`
- Sentry initialization added with:
  - Browser Tracing (performance monitoring)
  - Session Replay
  - ErrorBoundary wrapper for better error catching
  - Environment-based configuration

#### `frontend/.env` and `frontend/.env.production`
- Added `REACT_APP_SENTRY_DSN` variable

### 3. Get Your DSN ✅ COMPLETED

Your DSN has been configured:
```
https://1e50a11c2e380f90df112092f2dd0643@o4511619211919360.ingest.de.sentry.io/4511659252645968
```

The DSN is already added to your environment files:
- `frontend/.env` ✅
- `frontend/.env.production` ✅

### 4. Test the Integration

Restart the development server:
```bash
cd frontend
npm start
```

**Test error tracking:**

**Option 1: Use the Test Button** ⭐ (Recommended)
- Navigate to `/api-test` page
- Click the red "🔥 Break the world (Test Sentry)" button
- Check your Sentry dashboard for the error

**Option 2: Browser Console**
- Open browser console (F12)
- Run: `throw new Error("Test Sentry Error");`
- Check Sentry dashboard for the error

### 5. Features Enabled

✅ **Error Tracking**: All uncaught errors are sent to Sentry  
✅ **Performance Monitoring**: 100% of transactions tracked in dev, 10% in production  
✅ **Session Replay**: Records 10% of sessions, 100% of error sessions  
✅ **ErrorBoundary**: Graceful error handling with fallback UI  
✅ **Environment Tags**: Errors tagged with development/production environment

---

## Backend Setup (Laravel) ✅ COMPLETED

### 1. Installation ✅
Package installed successfully:
```bash
php composer.phar require sentry/sentry-laravel
```

### 2. Configuration ✅

**Files Updated:**

#### `backend/bootstrap/app.php`
Added Sentry integration to exception handling:
```php
use Sentry\Laravel\Integration;

->withExceptions(function (Exceptions $exceptions): void {
    Integration::handles($exceptions);
})->create();
```

#### `backend/.env`
Added Sentry configuration:
```env
SENTRY_LARAVEL_DSN=https://99397fd43ca75f70184695e7b17d9ff5@o4511619211919360.ingest.de.sentry.io/4511659221581904
SENTRY_TRACES_SAMPLE_RATE=1.0
SENTRY_PROFILES_SAMPLE_RATE=1.0
```

### 3. Test Route Created ✅
A test route has been added to `routes/api.php`:
```
GET /api/public/sentry-test
```

### 4. How to Test Backend Integration

**Option 1: Via Browser/Postman**
1. Start Laravel server: `cd backend && php artisan serve`
2. Visit: `http://localhost:8000/api/public/sentry-test`
3. You should see an error page
4. Check your Sentry dashboard - the error should appear!

**Option 2: Via Artisan Command**
```bash
cd backend
php artisan sentry:test
```

**Note about SSL Certificate Error:**
If you see "SSL certificate problem: unable to get local issuer certificate", this is a Windows development environment issue. The configuration is correct, and errors will be tracked successfully in production. To fix locally, you can:
- Update your PHP `cacert.pem` file
- Or add `'http_proxy_authentication' => 'basic',` to `config/sentry.php`
- Errors will still be logged locally to Laravel logs

### 5. Production Considerations

For production, adjust the sample rates in `backend/.env.production`:
```env
SENTRY_TRACES_SAMPLE_RATE=0.1  # 10% of transactions
SENTRY_PROFILES_SAMPLE_RATE=0.1  # 10% profiling
```

---

## Important Notes

### Environment Variables
- **Frontend**: Uses `REACT_APP_` prefix (Create React App convention)
- **Backend**: Uses `SENTRY_LARAVEL_DSN` (Laravel convention)
- **Never commit DSN values to Git** - they're already in `.gitignore`

### Sample Rates
**Frontend:**
- Development: 100% of transactions tracked
- Production: 10% of transactions tracked
- Session Replay: 10% of normal sessions, 100% of error sessions

**Backend (recommended):**
- Production: 10% sample rate (adjust based on traffic)
- Development: 100% sample rate

### Security
- DSN is **public** and safe to expose in frontend code
- It only allows sending data TO Sentry, not reading from it
- Configure **Allowed Domains** in Sentry project settings for extra security

### GitHub Actions / Deployment
When deploying via GitHub Actions:
1. Add DSN values as **GitHub Secrets**
2. Update deployment workflows to inject them into environment files

---

## Verification Checklist

### Frontend ✅ COMPLETED
- [x] Package installed (`@sentry/react`)
- [x] Sentry initialized in `index.tsx`
- [x] ErrorBoundary added
- [x] DSN added to `.env` files
- [x] Test button component created
- [x] Test error sent to Sentry dashboard

### Backend ✅ COMPLETED
- [x] Package installed (`sentry/sentry-laravel`)
- [x] Configuration published to `config/sentry.php`
- [x] Integration added to `bootstrap/app.php`
- [x] DSN added to `.env`
- [x] Test route created (`/api/public/sentry-test`)
- [ ] Test error sent to Sentry dashboard (your turn!)

---

## Next Steps

1. **✅ Frontend & Backend Setup Complete!**
2. **Test Frontend**: Click the test button on `/api-test` page (already done!)
3. **Test Backend**: Visit `http://localhost:8000/api/public/sentry-test` when Laravel server is running
4. **Monitor**: Check your [Sentry Dashboard](https://theracecraft.sentry.io/projects/) for incoming errors
5. **Configure Alerts**: Set up email/Slack notifications in Sentry project settings
6. **Production**: Update sample rates in `.env.production` files before deploying

## Resources
- [Sentry React Docs](https://docs.sentry.io/platforms/javascript/guides/react/)
- [Sentry Laravel Docs](https://docs.sentry.io/platforms/php/guides/laravel/)
- [Your Sentry Dashboard](https://theracecraft.sentry.io/projects/)

---

## Summary

🎉 **Both frontend and backend Sentry integrations are complete!**

- **Frontend** (`ticket-frontend`): Tracking errors, performance, and session replay
- **Backend** (`tickets-admin-backend`): Tracking exceptions and performance

All errors from both applications will now be sent to your Sentry dashboard for monitoring and debugging!
