# Sentry Error Tracking - Implementation & Testing Guide

## 📊 Implementation Status

### ✅ Frontend (React) - tickets.tfcmockup.com
- **Package**: `@sentry/react` v10.62.0
- **DSN**: Configured in `.env.production`
- **Features Enabled**:
  - Browser Tracing Integration
  - Session Replay Integration
  - Error Boundary Wrapper
  - Custom error capturing
  
- **Configuration**:
  ```typescript
  Sentry.init({
    dsn: "https://1e50a11c2e380f90df112092f2dd0643@o4511619211919360.ingest.de.sentry.io/4511659252645968",
    integrations: [
      Sentry.browserTracingIntegration(),
      Sentry.replayIntegration(),
    ],
    tracesSampleRate: 0.1 (production) / 1.0 (development),
    replaysSessionSampleRate: 0.1,
    replaysOnErrorSampleRate: 1.0,
    environment: "production"
  });
  ```

### ✅ Backend (Laravel) - admin.tfcmockup.com
- **Package**: `sentry/sentry-laravel` v4.26
- **DSN**: Configured in production `.env`
- **Configuration**:
  ```
  SENTRY_LARAVEL_DSN=https://99397fd43ca75f70184695e7b17d9ff5@o4511619211919360.ingest.de.sentry.io/4511659221581904
  ```

## 🧪 Testing Instructions

### Frontend Testing
1. **Visit Test Page**: https://tickets.tfcmockup.com/sentry-test
2. **Available Tests**:
   - **Test 1**: Caught Error - Manually captured exception
   - **Test 2**: Uncaught Error - Error boundary test
   - **Test 3**: Message Capture - Custom message logging
   - **Test 4**: Breadcrumb - Context tracking

3. **Expected Behavior**:
   - Each button click sends data to Sentry
   - You'll see an alert confirming the action
   - Check Sentry dashboard for captured events

### Backend Testing
1. **Test Exception**: 
   ```
   GET https://admin.tfcmockup.com/api/sentry-test?type=exception
   ```
   - Throws a test exception that Sentry captures

2. **Test Error**:
   ```
   GET https://admin.tfcmockup.com/api/sentry-test?type=error
   ```
   - Triggers a PHP error

3. **Test Message**:
   ```
   GET https://admin.tfcmockup.com/api/sentry-test?type=message
   ```
   - Sends custom message to Sentry
   - Returns: `{"message": "Test message sent to Sentry"}`

4. **Check Configuration**:
   ```
   GET https://admin.tfcmockup.com/api/sentry-test?type=info
   ```
   - Returns Sentry configuration status
   - Expected response:
     ```json
     {
       "sentry_dsn": "Configured",
       "environment": "production",
       "release": null,
       "sample_rate": 1.0
     }
     ```

## 📱 Verifying in Sentry Dashboard

1. **Login to Sentry**: https://sentry.io/
2. **Select Project**:
   - Frontend: `ticket-frontend`
   - Backend: `ticket-backend`
3. **Check Issues**:
   - Go to **Issues** tab
   - You should see test errors appear within seconds
4. **Check Performance**:
   - Go to **Performance** tab
   - View traces and transactions
5. **Check Session Replay** (Frontend only):
   - Go to **Replays** tab
   - View recorded sessions when errors occur

## 🔍 What to Look For

### In Sentry Issues:
- **Error Type**: Exception/Error name
- **Error Message**: "Test Error: Sentry is working!" or similar
- **Timestamp**: Should match when you triggered the test
- **Environment**: "production"
- **Browser/Platform**: User agent info (frontend) or PHP version (backend)
- **Stack Trace**: Full call stack showing where error occurred
- **Breadcrumbs**: User actions leading to error (if Test 4 was used)

### In Sentry Performance:
- **Transactions**: Page loads, API calls
- **Spans**: Individual operations within transactions
- **Response Times**: Latency data
- **Throughput**: Request volume

### In Session Replay (Frontend):
- **User Sessions**: Actual user interactions recorded
- **Error Sessions**: Sessions where errors occurred (100% capture rate)
- **Normal Sessions**: Random 10% of regular sessions

## 🚀 Production Usage

### Frontend (Automatic):
- All unhandled errors are automatically captured
- React component errors caught by Error Boundary
- Network errors and failed API calls tracked
- User session replays on errors

### Backend (Automatic):
- All exceptions are automatically captured
- Failed database queries tracked
- 500 errors logged to Sentry
- Queue job failures captured

### Manual Capturing (Optional):

**Frontend**:
```typescript
import * as Sentry from '@sentry/react';

// Capture exception
try {
  // risky code
} catch (error) {
  Sentry.captureException(error);
}

// Capture message
Sentry.captureMessage('Something important happened', 'info');

// Add breadcrumb
Sentry.addBreadcrumb({
  message: 'User clicked checkout button',
  level: 'info',
  category: 'user-action',
});
```

**Backend**:
```php
use Sentry\Laravel\Facade as Sentry;

// Capture exception
try {
    // risky code
} catch (\Exception $e) {
    Sentry::captureException($e);
}

// Capture message
Sentry::captureMessage('Something important happened', \Sentry\Severity::info());
```

## 📊 Sample Rates Explained

### Frontend:
- **Traces**: 10% of transactions tracked (to reduce quota usage)
- **Session Replays**: 10% of normal sessions, 100% of error sessions
- **Errors**: 100% of all errors captured

### Backend:
- **Traces**: 100% of transactions (can be reduced if needed)
- **Errors**: 100% of all errors captured

## 🔧 Troubleshooting

### If errors are not appearing in Sentry:

1. **Check DSN Configuration**:
   - Frontend: Visit `/sentry-test` and check status alert
   - Backend: Visit `/api/sentry-test?type=info`

2. **Check Browser Console** (Frontend):
   - Look for Sentry initialization messages
   - Check for CORS or network errors

3. **Check Laravel Logs** (Backend):
   - `ssh root@168.144.157.119`
   - `tail -f /var/www/tickets-backend/backend/storage/logs/laravel.log`

4. **Verify Environment**:
   - Errors only sent in production environment
   - Check `APP_ENV` and `NODE_ENV` settings

5. **Check Sentry Quota**:
   - Login to Sentry dashboard
   - Check if quota is exceeded
   - Free tier: 5,000 errors/month per project

## 📝 Notes

- Test pages are accessible in production for verification purposes
- You can remove `/sentry-test` routes after verification if desired
- Sentry captures are non-blocking (asynchronous)
- Error capturing has minimal performance impact
- Session replays increase bandwidth usage slightly

## 🎯 Quick Test Commands

```bash
# Frontend test
curl https://tickets.tfcmockup.com/sentry-test

# Backend exception test
curl https://admin.tfcmockup.com/api/sentry-test?type=exception

# Backend config check
curl https://admin.tfcmockup.com/api/sentry-test?type=info
```

## ✅ Deployment Status

- ✅ Frontend Sentry initialized in `src/index.tsx`
- ✅ Frontend test page deployed at `/sentry-test`
- ✅ Backend Sentry DSN configured in production `.env`
- ✅ Backend test endpoint deployed at `/api/sentry-test`
- ✅ Laravel cache cleared and rebuilt
- ✅ Both deployments pushed to GitHub and auto-deployed

## 📧 Sentry Projects

- **Organization**: o4511619211919360
- **Frontend Project**: 4511659252645968
- **Backend Project**: 4511659221581904
- **Region**: Europe (de.sentry.io)
