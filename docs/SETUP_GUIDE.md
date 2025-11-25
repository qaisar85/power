# Platform Setup & Deployment Guide

## Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and NPM
- MySQL 8.0 or higher
- Redis (optional, for caching)
- Git

## Initial Setup

### 1. Clone and Install Dependencies

```bash
# Clone repository (if not already cloned)
git clone <repository-url>
cd Power

# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 2. Environment Configuration

```bash
# Copy environment file
copy .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Configure Database

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=power
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Run Migrations

```bash
# Run all migrations
php artisan migrate

# (Optional) Seed database with sample data
php artisan db:seed
```

### 5. Configure Payment Gateways

Edit `.env`:

```env
# Stripe
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

# PayPal
PAYPAL_MODE=sandbox
PAYPAL_CLIENT_ID=...
PAYPAL_SECRET=...
```

### 6. Configure File Storage

```bash
# Link storage directory
php artisan storage:link
```

For production, configure S3 or equivalent:

```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket
```

### 7. Build Frontend Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### 8. Set Up Queue Workers

```bash
# Test queue worker
php artisan queue:work

# For production, use supervisor
```

Create supervisor config `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker.log
```

## Running the Application

### Development

```bash
# Option 1: Built-in server
php artisan serve

# Option 2: Laravel Boost (recommended for development)
composer run dev
```

Access: `http://localhost:8000`

### Production

Use a web server like Nginx or Apache.

Example Nginx configuration:

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/Power/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Regional Agent System Setup

### 1. Run Agent Migrations

```bash
php artisan migrate --path=/database/migrations/2025_10_21_000001_create_regional_agents_table.php
php artisan migrate --path=/database/migrations/2025_10_21_000002_create_agent_services_and_revenue_tables.php
```

### 2. Create First Agent (Console/Tinker)

```bash
php artisan tinker
```

```php
$user = \App\Models\User::find(1); // or create new user

$agent = \App\Models\RegionalAgent::create([
    'user_id' => $user->id,
    'region_type' => 'city',
    'country_id' => 1,
    'city_id' => 1,
    'commission_rate' => 10.00,
    'business_name' => 'Dubai Equipment Services',
    'business_description' => 'Professional equipment services',
    'service_types' => ['listing_support', 'verification', 'logistics'],
    'supported_categories' => ['drilling', 'power_generation'],
    'languages' => ['en', 'ar'],
    'is_verified' => true,
    'is_active' => true,
]);
```

### 3. Grant Agent Permissions

```php
$user->assignRole('regional_agent');
```

## Multi-Currency Setup

### 1. Seed Currency Rates

```bash
# Create seeder or manually insert
```

Example:

```php
\App\Models\CurrencyRate::create([
    'from_currency' => 'USD',
    'to_currency' => 'EUR',
    'rate' => 0.92,
    'updated_at' => now(),
]);
```

### 2. Schedule Automatic Updates

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('currency:update')->daily();
}
```

## Mobile API Setup

The API is automatically available at `/api/v1/*`

### 1. Test API Authentication

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'
```

### 2. Test Protected Endpoint

```bash
curl -X GET http://localhost:8000/api/v1/listings \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 3. Configure Rate Limiting

Edit `config/sanctum.php`:

```php
'limiter' => 'api',
```

Edit `app/Http/Kernel.php`:

```php
'api' => [
    'throttle:300,1', // 300 requests per minute
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

## Localization Setup

### 1. Supported Languages

Current: `en`, `ru`, `ar`, `fr`

### 2. Add New Language

```bash
# Copy English translations
cp -r lang/en lang/es

# Edit translations in lang/es/*
```

### 3. RTL Support

For Arabic/Hebrew, ensure CSS includes RTL styles:

```css
[dir="rtl"] {
  /* RTL-specific styles */
}
```

## Performance Optimization

### 1. Enable OpCache

Edit `php.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
```

### 2. Configure Redis Caching

Edit `.env`:

```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 3. Optimize Laravel

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

## Security Configuration

### 1. Set Secure Environment

```env
APP_ENV=production
APP_DEBUG=false
```

### 2. Configure CORS (if using API)

Install package:

```bash
composer require fruitcake/laravel-cors
```

Edit `config/cors.php` as needed.

### 3. Enable HTTPS

```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;
    
    # ... rest of config
}
```

### 4. Set Up Firewall

```bash
# Allow only necessary ports
ufw allow 22
ufw allow 80
ufw allow 443
ufw enable
```

## Monitoring & Logging

### 1. Configure Logging

Edit `config/logging.php`:

```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['daily', 'slack'],
    ],
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => 'error',
        'days' => 14,
    ],
],
```

### 2. Install Sentry (optional)

```bash
composer require sentry/sentry-laravel
```

### 3. Set Up Laravel Telescope (development only)

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

Access: `http://localhost:8000/telescope`

## Backup Strategy

### 1. Database Backups

```bash
# Install backup package
composer require spatie/laravel-backup

# Configure backup
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"

# Run backup
php artisan backup:run
```

Schedule daily backups in `Kernel.php`:

```php
$schedule->command('backup:clean')->daily()->at('01:00');
$schedule->command('backup:run')->daily()->at('02:00');
```

### 2. File Storage Backups

Use S3 versioning or equivalent for automatic file backups.

## Testing

### 1. Run Tests

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter ListingTest
```

### 2. Create New Tests

```bash
php artisan make:test AgentServiceTest
```

## Deployment Checklist

- [ ] Update `.env` with production credentials
- [ ] Run `composer install --no-dev --optimize-autoloader`
- [ ] Run `npm run build`
- [ ] Run `php artisan migrate --force`
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Set file permissions (755 for directories, 644 for files)
- [ ] Set storage and cache directories to 775
- [ ] Configure queue workers with supervisor
- [ ] Set up cron jobs for scheduled tasks
- [ ] Configure SSL certificate
- [ ] Set up monitoring and alerts
- [ ] Test payment gateways in production
- [ ] Test mobile API endpoints
- [ ] Verify webhook endpoints are accessible
- [ ] Load test the application

## Scaling Considerations

### Horizontal Scaling

1. **Load Balancer**: Use Nginx, HAProxy, or cloud load balancer
2. **Multiple App Servers**: Deploy application on multiple servers
3. **Shared Session Storage**: Use Redis for sessions
4. **Centralized File Storage**: Use S3 or equivalent
5. **Database Read Replicas**: Configure MySQL replication

### Database Optimization

```sql
-- Add indexes for frequently queried columns
CREATE INDEX idx_listings_status ON listings(status);
CREATE INDEX idx_listings_type_category ON listings(type, category);
CREATE INDEX idx_regional_agents_location ON regional_agents(country_id, is_active);
```

### CDN Integration

Configure CDN for static assets:

```env
ASSET_URL=https://cdn.yourdomain.com
```

## Troubleshooting

### Common Issues

**Issue**: 500 Error on production
**Solution**: Check `storage/logs/laravel.log`, ensure file permissions are correct

**Issue**: Queue jobs not processing
**Solution**: Restart queue worker, check supervisor status

**Issue**: API returns 401 Unauthorized
**Solution**: Check Sanctum configuration, verify token is valid

**Issue**: Images not displaying
**Solution**: Run `php artisan storage:link`, check file permissions

## Support & Resources

- **Documentation**: `/docs` folder
- **API Documentation**: `/docs/API_DOCUMENTATION.md`
- **Architecture**: `/docs/ARCHITECTURE.md`

## Maintenance

### Regular Tasks

- Monitor disk space
- Review error logs
- Update dependencies monthly
- Backup database weekly
- Review and optimize slow queries
- Update currency rates
- Review moderation queue

### Updates

```bash
# Update dependencies
composer update
npm update

# Run migrations
php artisan migrate

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```
