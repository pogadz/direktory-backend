# Admin UI — Roles & Permissions

Built with React + Inertia.js. Accessible at `/admin/*`.

---

## URLs

| URL | Description |
|-----|-------------|
| `/admin/login` | Login page |
| `/admin/dashboard` | Dashboard (stats) |
| `/admin/roles` | List all roles |
| `/admin/roles/create` | Create a new role |
| `/admin/roles/{id}/edit` | Edit role + assign permissions |
| `/admin/permissions` | List all permissions (grouped by category) |
| `/admin/permissions/create` | Create a new permission |
| `/admin/permissions/{id}/edit` | Edit a permission |

---

## How to Log In

You must log in with a user who has a profile assigned the **`admin`** role.

To find or verify an admin user via tinker:

```bash
php artisan tinker
```

```php
Profile::whereHas('roles', fn($q) => $q->where('name', 'admin'))->with('user')->first();
```

---

## Managing Permissions

### Create a Permission

1. Go to `/admin/permissions/create`
2. Fill in:
   - **Name** — slug used in code (e.g. `booking.create`)
   - **Display Name** — human-readable label (e.g. `Create Booking`)
   - **Description** — optional
   - **Category** — groups permissions in the UI (e.g. `booking`); autocompletes from existing categories
3. Submit

### Edit a Permission

1. Go to `/admin/permissions`
2. Click **Edit** on any permission
3. Update fields and save
4. The edit page also shows which roles currently have this permission

### Delete a Permission

- Click **Delete** on the permissions list — a confirmation dialog appears before deletion

---

## Managing Roles

### Create a Role

1. Go to `/admin/roles/create`
2. Fill in name (slug), display name, and description
3. Check off permissions from the grouped checklist — each category has a **select-all** checkbox
4. Submit

### Edit a Role

Go to `/admin/roles/{id}/edit`. The page has two independent forms:

- **Details** — update name, display name, description
- **Permissions** — grouped checklist; hit *Save Permissions* to sync (replaces the full permission set)

> System roles (`admin`, `user`, `worker`) display a warning and their details cannot be modified.

### Delete a Role

- Click **Delete** on the roles list
- System roles (`is_system_role = true`) cannot be deleted — the button is hidden for them

---

## How It Connects to the API

Roles and permissions created here are immediately enforced in the API — they share the same database tables (`roles`, `permissions`, `role_permission`, `profile_role`).

Both the API middleware (`CheckPermission`) and the admin middleware (`CheckAdminPermission`) call the same `Profile::hasAnyPermission()` method, so any changes here are reflected across the entire app automatically.

### Applying a Permission to an API Route

After creating a permission in the UI (e.g. `booking.create`):

```php
// routes/api.php
Route::post('/', [BookingController::class, 'store'])
    ->middleware('permission:booking.create');
```

---

## Giving a User Admin Access

A user needs a profile with the `admin` role to access the admin UI:

```php
// php artisan tinker
$profile  = Profile::where('user_id', $userId)->first();
$adminRole = Role::where('name', 'admin')->first();
$profile->roles()->syncWithoutDetaching([$adminRole->id]);
```

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Server-side rendering bridge | [Inertia.js](https://inertiajs.com) (`inertiajs/inertia-laravel`) |
| Frontend framework | React 18 (`@inertiajs/react`) |
| Styling | Tailwind CSS |
| Auth | Laravel session (`web` guard) — separate from the Sanctum API tokens |
| Build tool | Vite (`@vitejs/plugin-react`) |

---

## File Structure

```
app/Http/Controllers/Admin/
  AdminAuthController.php        # Login / logout
  AdminDashboardController.php   # Dashboard stats
  AdminRoleController.php        # Roles CRUD + permission sync
  AdminPermissionController.php  # Permissions CRUD

app/Http/Middleware/
  HandleInertiaRequests.php      # Shares auth.user + flash with all Inertia pages
  CheckAdminPermission.php       # Session-based permission check for admin routes

routes/
  admin.php                      # All /admin/* routes

resources/views/
  admin.blade.php                # Inertia root view

resources/js/
  admin.jsx                      # React entry point
  Layouts/AdminLayout.jsx        # Sidebar layout with nav + flash messages
  Components/PermissionChecklist.jsx  # Grouped checkbox component with select-all
  Pages/Admin/
    Login.jsx
    Dashboard.jsx
    Roles/
      Index.jsx
      Create.jsx
      Edit.jsx
    Permissions/
      Index.jsx
      Create.jsx
      Edit.jsx
```
