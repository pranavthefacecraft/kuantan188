# Deployment Guide for Subdomain Structure

## Domain Structure
- **Main**: yourdomain.com (WordPress)
- **Backend**: admin.yourdomain.com (Laravel Admin + API)
- **Frontend**: tickets.yourdomain.com (React Public App)

## Backend Deployment (admin.yourdomain.com)

### 1. Server Requirements
- PHP 8.1+
- MySQL 8.0+
- Composer
- Nginx/Apache

### 2. Deployment Steps
```bash
# Upload backend files to admin.yourdomain.com root
# Copy .env.production to .env and configure
cp .env.production .env

# Install dependencies
composer install --no-dev --optimize-autoloader

# Generate application key
php artisan key:generate

# Run migrations and seeders
php artisan migrate --force
php artisan db:seed --class=CountrySeeder
php artisan db:seed --class=EventSeeder
php artisan db:seed --class=AdminUserSeeder

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 3. Nginx Configuration (admin.yourdomain.com)
```nginx
server {
    listen 80;
    listen 443 ssl http2;
    server_name admin.yourdomain.com;
    
    ssl_certificate /path/to/ssl/cert.pem;
    ssl_certificate_key /path/to/ssl/private.key;
    
    root /var/www/admin.yourdomain.com/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## Frontend Deployment (tickets.yourdomain.com)

### 1. Build Process
```bash
# Install dependencies
npm ci

# Build for production
npm run build

# Upload build folder contents to tickets.yourdomain.com
```

### 2. Nginx Configuration (tickets.yourdomain.com)
```nginx
server {
    listen 80;
    listen 443 ssl http2;
    server_name tickets.yourdomain.com;
    
    ssl_certificate /path/to/ssl/cert.pem;
    ssl_certificate_key /path/to/ssl/private.key;
    
    root /var/www/tickets.yourdomain.com;
    index index.html;
    
    # Handle client-side routing
    location / {
        try_files $uri $uri/ /index.html;
    }
    
    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

## Security Checklist

### Backend Security
- [ ] Generate new APP_KEY for production
- [ ] Set APP_DEBUG=false
- [ ] Configure proper database credentials
- [ ] Set up SSL certificates
- [ ] Configure CORS for tickets.yourdomain.com
- [ ] Set proper file permissions

### Frontend Security
- [ ] Remove source maps in production
- [ ] Set up SSL certificate
- [ ] Configure proper CORS headers
- [ ] Validate all API endpoints

## Post-Deployment Testing
1. Test API endpoints: https://admin.yourdomain.com/api/public/events
2. Test frontend: https://tickets.yourdomain.com
3. Test reservation modal functionality
4. Verify CORS is working between subdomains
5. Check SSL certificates for both domains

## Monitoring & Maintenance
- Set up error logging for Laravel
- Configure monitoring for both subdomains
- Set up automated backups for database
- Monitor API performance and usage