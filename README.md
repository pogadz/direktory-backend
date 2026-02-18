# Laravel API Backend with Multi-Account & Dynamic Roles

A secure Laravel 12 API backend with token-based authentication, multi-account support, and dynamic role/permission management.

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

## 🐳 Starting Local Server with Docker
Edit your `.env` file with your database credentials:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

# add this if you want to seed an admin account
SEEDER_ADMIN_EMAIL=admin@example.com
SEEDER_ADMIN_PASSWORD=your_password_here
```

```bash
# Quick setup (add --no-cache argument if you dont to build without cache)
docker compose build

# And then run this and it should run the application
docker compose up -d

# If you want to seed admin data you can run this
docker exec direktory-app php artisan db:seed --class=AdminUserSeeder
```

Access at: http://localhost:8000.

### Test the System

```bash
# 1. Login as admin
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "User-Agent: MyApp/1.0" \
  -d '{
    "email": "admin@example.com",
    "password": "testing123"
  }'

# 2. Get your accounts
curl -X GET http://localhost:8000/api/accounts \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -H "User-Agent: MyApp/1.0"

# 3. Switch to admin account (get new token!)
curl -X POST http://localhost:8000/api/accounts/switch \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "User-Agent: MyApp/1.0" \
  -d '{"account_id": 1}'

# 4. Create a custom role (admin only)
curl -X POST http://localhost:8000/api/roles \
  -H "Authorization: Bearer NEW_TOKEN_FROM_SWITCH" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "User-Agent: MyApp/1.0" \
  -d '{
    "name": "moderator",
    "display_name": "Moderator",
    "description": "Content moderation role",
    "permission_ids": [9, 10, 11]
  }'
```

### API Base URL

```
http://127.0.0.1:8000/api

# Test endpoint
http://127.0.0.1:8000/api/test
```

## 🗂️ System Architecture

```
User → Multiple Accounts → Multiple Roles → Multiple Permissions
```

**Example:**
- John Doe (user)
  - Work Account → [manager role] → [view/create/edit/delete content, view reports]
  - Personal Account → [viewer role] → [view content only]

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


## 🎯 Default Permissions

**5 Categories | 18 Permissions:**

- **User Management** (4): view, create, edit, delete users
- **Role Management** (4): view roles, manage roles, manage permissions, assign roles
- **Content Management** (5): view, create, edit, delete, publish content
- **Dashboard** (3): view dashboard, view reports, export data
- **Settings** (2): view settings, manage settings

## 🔌 Main API Endpoints

### Authentication
```
POST   /api/register          # Register
POST   /api/login             # Login
POST   /api/logout            # Logout
POST   /api/refresh           # Refresh token
GET    /api/user              # Get user info
```

### Account Management
```
GET    /api/accounts          # List accounts
POST   /api/accounts          # Create account
POST   /api/accounts/switch   # Switch account (get new token!)
PUT    /api/accounts/{id}     # Update account
DELETE /api/accounts/{id}     # Delete account
```

### Role Management (Admin Only)
```
GET    /api/roles             # List roles
POST   /api/roles             # Create role
PUT    /api/roles/{id}        # Update role
DELETE /api/roles/{id}        # Delete role
POST   /api/roles/{id}/permissions/sync    # Assign permissions
```

### Permission Management (Admin Only)
```
GET    /api/permissions                # List permissions
GET    /api/permissions/by-category    # Group by category
POST   /api/permissions                # Create permission
PUT    /api/permissions/{id}           # Update permission
DELETE /api/permissions/{id}           # Delete permission
```

### Account-Role Assignment
```
GET    /api/accounts/{id}/roles              # Get roles
POST   /api/accounts/{id}/roles/assign       # Assign roles
POST   /api/accounts/{id}/roles/revoke       # Remove roles
POST   /api/accounts/{id}/roles/sync         # Sync roles
GET    /api/accounts/{id}/roles/permissions  # Get all permissions
```

## 🛡️ Middleware Usage

```php
// Permission-based protection (recommended)
Route::middleware(['auth:sanctum', 'permission:edit-users'])->group(function () {
    Route::put('/users/{id}', [UserController::class, 'update']);
});

// Multiple permissions (OR logic)
Route::middleware(['auth:sanctum', 'permission:edit-users,delete-users'])->group(function () {
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});

// Role-based protection (legacy)
Route::middleware(['auth:sanctum', 'account.role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
});
```

## 📂 Project Structure

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── AuthController.php          # Authentication
│   │   │   ├── AccountController.php       # Account CRUD
│   │   │   ├── AccountRoleController.php   # Account-role management
│   │   │   ├── RoleController.php          # Role CRUD (admin)
│   │   │   └── PermissionController.php    # Permission CRUD (admin)
│   │   └── Middleware/
│   │       ├── ValidateApiRequest.php      # API validation
│   │       ├── CheckAccountRole.php        # Role middleware
│   │       └── CheckPermission.php         # Permission middleware
│   └── Models/
│       ├── User.php                        # User with accounts
│       ├── Account.php                     # Account with roles
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
├── SETUP_INSTRUCTIONS.md                  # Setup guide
├── SEEDING_GUIDE.md                       # Seeding guide
├── TEST_ACCOUNTS.md                       # Test credentials
├── ACCOUNTS_GUIDE.md                      # Account API docs
├── ROLES_PERMISSIONS_GUIDE.md             # Role/Permission API docs
└── README.md                              # This file
```

## Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/AuthTest.php
```

---

**Built with Laravel 11**
