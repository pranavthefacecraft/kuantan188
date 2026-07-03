# Ticket Image Fix - Deployment Guide

## Problem
Ticket images were returning 404 errors with URLs like:
`https://admin.tfcmockup.com/storage/tickets/ticket_1777550846_69f345fe4394b.jpeg`

## Root Cause
- Images were being stored to `public/storage/tickets/` directory
- The `public/storage` directory doesn't exist (symlink not created)
- Events successfully use `public/uploads/events/` which exists

## Solution Applied
Changed ticket image storage to use `uploads/tickets/` instead of `storage/tickets/`, matching the working events pattern.

## Changes Made

### 1. Backend Code Updates
**File**: `backend/app/Http/Controllers/AdminDashboardController.php`

- **Store Ticket** (line ~815): Changed from `storage/tickets` to `uploads/tickets`
- **Update Ticket** (line ~995): Changed from `storage/tickets` to `uploads/tickets`
- Both now include automatic directory creation if not exists

### 2. Database Migration
**File**: `backend/database/migrations/2026_07_03_update_ticket_image_paths.php`

Migration automatically updates all existing ticket records:
- Changes `storage/tickets/` → `uploads/tickets/` in database
- Handles both relative and absolute paths

### 3. Directory Structure
Created: `backend/public/uploads/tickets/` directory

## Deployment Steps for Production

### Step 1: Pull Latest Code
```bash
cd /path/to/backend
git pull origin main
```

### Step 2: Create Directory on Server
```bash
# SSH to admin.tfcmockup.com
mkdir -p public/uploads/tickets
chmod 755 public/uploads/tickets
```

### Step 3: Run Migration
```bash
cd /path/to/backend
php artisan migrate
```

This will automatically update all existing ticket image paths in the database.

### Step 4: Move Existing Images (if any)
If images were already uploaded to the old location:
```bash
# If public/storage/tickets/ exists and has images
if [ -d "public/storage/tickets" ]; then
    cp -r public/storage/tickets/* public/uploads/tickets/ 2>/dev/null || true
    echo "Images copied from storage/tickets to uploads/tickets"
fi
```

### Step 5: Verify Changes
1. Check directory exists and has correct permissions:
   ```bash
   ls -la public/uploads/
   ```

2. Test ticket upload in admin panel:
   - Go to admin dashboard → Tickets
   - Create or edit a ticket
   - Upload an image
   - Verify image URL in database is `uploads/tickets/...`

3. Test frontend display:
   - Visit frontend ticket pages
   - Verify images load correctly
   - Check browser console for 404 errors (should be none)

4. Check API response:
   ```bash
   curl https://admin.tfcmockup.com/api/public/tickets | jq '.data[].image_url'
   ```
   Should return URLs like: `https://admin.tfcmockup.com/uploads/tickets/...`

## Verification Checklist

- [ ] Directory `public/uploads/tickets/` exists with 755 permissions
- [ ] Migration ran successfully (check `migrations` table)
- [ ] Database `tickets.image_url` values updated (check with query)
- [ ] New ticket uploads save to `uploads/tickets/`
- [ ] Images display correctly in frontend
- [ ] No 404 errors in browser console
- [ ] API returns correct image URLs

## Database Verification Query

To check if paths were updated correctly:
```sql
-- Check ticket image paths
SELECT id, ticket_name, image_url 
FROM tickets 
WHERE image_url IS NOT NULL;

-- Should see paths like: uploads/tickets/ticket_....jpeg
-- NOT: storage/tickets/ticket_....jpeg
```

## Rollback Plan (if needed)

If issues occur, rollback the migration:
```bash
php artisan migrate:rollback --step=1
```

This will revert image paths back to `storage/tickets/` in the database.

## Technical Notes

### Why uploads/ instead of storage/?
1. **Consistency**: Events already use `uploads/events/` successfully
2. **Simplicity**: Direct file access without symlink dependencies
3. **Deployment**: Easier to maintain across environments
4. **No Symlink**: Avoids Laravel storage symlink complexity

### URL Generation
The API already handles URL generation correctly:
- Relative paths (e.g., `uploads/tickets/file.jpg`) get domain prepended
- Full URLs (starting with `http`) are used as-is
- Result: `https://admin.tfcmockup.com/uploads/tickets/file.jpg`

### File Structure
```
backend/
├── public/
│   ├── uploads/
│   │   ├── events/          ← Event images (existing, working)
│   │   └── tickets/         ← Ticket images (NEW, now working)
│   └── index.php
└── storage/
    └── app/
        └── public/          ← Laravel storage (not used for tickets/events)
```

## GitHub Actions Note

The GitHub Actions workflow should automatically deploy these changes when pushed to the `backend/` directory. The migration will run as part of the deployment process.

## Testing URLs

After deployment, test these endpoints:
- **Tickets API**: https://admin.tfcmockup.com/api/public/tickets
- **Single Ticket**: https://admin.tfcmockup.com/api/public/tickets/{id}
- **Frontend**: https://tickets.tfcmockup.com/tickets

Check that all image URLs return 200 status (not 404).

## Support

If images still don't load after deployment:
1. Check web server configuration (nginx/apache) serves files from `public/uploads/`
2. Verify file permissions (644 for files, 755 for directories)
3. Check .htaccess or nginx config for any blocks on `uploads/` directory
4. Verify files actually exist at the specified paths
