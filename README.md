# Laravel API Backend with Token Authentication

A secure Laravel 12 API backend with token-based authentication using Laravel Sanctum.

## Features

- 🔐 **Token-Based Authentication** - Secure API authentication using Laravel Sanctum
- 🚦 **Rate Limiting** - Protection against brute force and DDoS attacks
- 🌐 **CORS Enabled** - Cross-Origin Resource Sharing configured
- 📝 **RESTful API** - Clean API endpoints with JSON responses
- 🛡️ **Security Middleware** - User agent validation and request filtering
- ✅ **Input Validation** - Comprehensive request validation
- 🔄 **Token Management** - Login, logout, and token revocation

## Requirements

- PHP 8.2 or higher
- Composer
- MySQL/PostgreSQL/SQLite
- Node.js & NPM (for asset compilation)

## Installation

### 1. Clone and Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 2. Environment Configuration

```bash
# Copy the example environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Configure Database

Edit your `.env` file with your database credentials:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

### 4. Run Migrations

```bash
# Run database migrations
php artisan migrate
```

### 5. Start Development Server

```bash
# Start Laravel development server
php artisan serve

# Server will start at: http://127.0.0.1:8000
```

## API Documentation

### Base URL

```
http://127.0.0.1:8000/api

# Api url for testing
http://127.0.0.1:8000/api/test
```

## Security Features

### Rate Limiting

- **Public routes** (register, login): 10 requests per minute
- **Protected routes** (user, logout): 60 requests per minute

Exceeding rate limits returns `429 Too Many Requests`.

### CORS Configuration

CORS is enabled for all origins by default. For production, update `config/cors.php`:

```php
'allowed_origins' => [
    'https://yourdomain.com',
    'https://app.yourdomain.com',
],
```

### Token Authentication & Expiration

- Tokens are unique per login session
- **Tokens expire after 60 minutes (1 hour) by default**
- Tokens can be refreshed before expiration using `/api/refresh` endpoint
- Tokens can be revoked individually via logout
- Configure expiration time in `config/sanctum.php` or via `SANCTUM_TOKEN_EXPIRATION` env variable
- All login/register responses include `expires_in` field (token lifetime in seconds)

**📖 For detailed token refresh implementation, see [TOKEN_REFRESH_GUIDE.md](TOKEN_REFRESH_GUIDE.md)**

### API Request Validation Middleware ✅ Enabled

The `ValidateApiRequest` middleware is **enabled** on all API routes. It provides:
- **User agent validation** - Blocks common scrapers (curl, wget, python-requests, scrapy, bots)
- **Accept header validation** - Requires `Accept: application/json` header

**Important for testing:**
- When using cURL, you'll be blocked unless you set a custom user agent
- Postman works fine (doesn't match blocked user agents)
- Always include `Accept: application/json` header in requests

To disable or modify, see [app/Http/Middleware/ValidateApiRequest.php](app/Http/Middleware/ValidateApiRequest.php).


## Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/AuthTest.php
```

## Development

### Code Formatting

```bash
# Format code with Laravel Pint
./vendor/bin/pint
```

### Database Seeding

```bash
# Run seeders
php artisan db:seed

# Fresh migration with seeding
php artisan migrate:fresh --seed
```

### Clear Caches

```bash
# Clear application cache
php artisan cache:clear

# Clear route cache
php artisan route:clear

# Clear config cache
php artisan config:clear

# Clear all caches
php artisan optimize:clear
```

## Troubleshooting

### "419 Page Expired" Error
Run: `php artisan config:clear`

### "SQLSTATE Connection Refused"
Check database credentials in `.env` and ensure database server is running.

### "Class not found" Error
Run: `composer dump-autoload`

### CORS Issues
Update `config/cors.php` with your frontend domain.

### Rate Limit Issues
Adjust rate limits in `routes/api.php` throttle middleware.

## Project Structure

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       └── AuthController.php    # API auth endpoints
│   │   └── Middleware/
│   │       └── ValidateApiRequest.php    # Security middleware
│   └── Models/
│       └── User.php                       # User model with HasApiTokens
├── config/
│   ├── cors.php                           # CORS configuration
│   └── sanctum.php                        # Sanctum configuration
├── database/
│   └── migrations/                        # Database migrations
├── routes/
│   ├── api.php                            # API routes
│   └── web.php                            # Web routes
├── .env.example                           # Environment template
└── README.md                              # This file
```

And that's it!
