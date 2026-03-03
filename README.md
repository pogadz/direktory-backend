# Direktory

## ✨ Key Features
- 🔐 **Token-Based Authentication** - Secure API authentication using Laravel Sanctum
- 👥 **Multi-Account System** - Users can create and manage multiple accounts
- 🎭 **Dynamic Role Management** - Admins can create custom roles with specific permissions
- 🔑 **Granular Permissions** - 18 default permissions with ability to create more
- 🚦 **Rate Limiting** - Protection against brute force and DDoS attacks
- 🌐 **CORS Enabled** - Cross-Origin Resource Sharing configured
- 📝 **RESTful API** - Clean API endpoints with JSON responses
- 🛡️ **Security Middleware** - Permission-based and role-based access control
- ✅ **Input Validation** - Comprehensive request validation
- 🔄 **Token Management** - Login, logout, refresh, and account switching

## Prerequisite
- [Docker](https://www.docker.com/)

## Configure Environment File
First, create/edit your `.env` file and paste the variables from .env.example. And then, edit the database credentials. 

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

SEEDER_PASSWORD=testing123
```

## 🐳 Starting Local Server with Docker

```bash
# Quick setup (add --no-cache argument if you want to build without cache)
docker compose build

# And then run this and it should run the application
docker compose up -d

# Run this if you want to populate some data in the database
docker exec direktory-app php artisan db:seed
```

Access at: http://localhost:8000.

### API Docs

Please run this command.
```
# Publish vendor
docker exec direktory-app php artisan vendor:publish --tag=scribe-config

# Generate or update docs
docker exec direktory-app php artisan scribe:generate
```
And you can acccess it in http://localhost:8000/docs.
You can also enable the `Try It Out` feature by adding this in .env file.
```
# Enable docs try it out feature
SCRIBE_TRY_IT_OUT=true
```


### API Base URL

```
http://127.0.0.1:8000/api

# Test endpoint
# You should see '{"error": "Invalid Accept header. Must accept application/json"}'
# which would mean that the api is accessable and working.
http://127.0.0.1:8000/api/test
```


## Security Features

### Rate Limiting

- **Public routes** (register, login): 10 requests per minute
- **Protected routes** (user, logout): 60 requests per minute

Exceeding rate limits returns `429 Too Many Requests`.

### CORS Configuration

Cors can be configured in .env file. Just add the urls that you want to be whitelisted.
```
# Added 2 urls as an example
CORS_ALLOWED_ORIGINS=https://workers-directory.vercel.app,http://localhost:3000
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


## 📂 Project Structure

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── AuthController.php          # Authentication
│   │   │   ├── ProfileController.php       # Profile CRUD
│   │   │   ├── ProfileRoleController.php   # Profile-role management
│   │   │   ├── RoleController.php          # Role CRUD (admin)
│   │   │   └── PermissionController.php    # Permission CRUD (admin)
│   │   └── Middleware/
│   │       ├── ValidateApiRequest.php      # API validation
│   │       ├── CheckProfileRole.php        # Role middleware
│   │       └── CheckPermission.php         # Permission middleware
│   └── Models/
│       ├── User.php                        # User with profiles
│       ├── Profile.php                     # Profile with roles
│       ├── Role.php                        # Role with permissions
│       └── Permission.php                  # Permission model
├── config/
│   ├── cors.php                            # CORS configuration
│   └── sanctum.php                         # Sanctum configuration
├── database/
│   ├── migrations/                         # All database tables
│   └── seeders/
│       ├── RolesAndPermissionsSeeder.php  # System setup
│       ├── AdminUserSeeder.php            # Admin user
│       └── NormalUsersSeeder.php          # 10 test users
├── routes/
│   ├── api.php                            # All API routes
│   └── web.php                            # Web routes
└── README.md                              # This file
```

## Test

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/AuthTest.php
```

## Troubleshoot
If you encounter disk space issue in docker just run the docker prune command and then rebuild.

```
docker system prune -a
docker compose build
docker compose up -d
```

---

**Built with Laravel 11**
