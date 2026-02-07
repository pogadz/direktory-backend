# Token Expiration and Refresh Guide

## Overview

This Laravel API uses **Laravel Sanctum** for token-based authentication with automatic expiration and refresh capabilities.

## Token Configuration

### Expiration Time

Tokens expire after **60 minutes (1 hour)** by default. Configure this in your `.env` file:

```env
SANCTUM_TOKEN_EXPIRATION=60  # minutes
```

Or directly in `config/sanctum.php`:

```php
'expiration' => 60, // minutes
```

### Token Response Format

When you login or register, the API returns:

```json
{
  "message": "Login successful",
  "user": { ... },
  "access_token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "token_type": "Bearer",
  "expires_in": 3600  // seconds (60 minutes * 60)
}
```

## API Endpoints

### 1. Register (with token expiration)

**POST** `/api/register`

**Response includes:**
- `expires_in`: Token lifetime in seconds

### 2. Login (with token expiration)

**POST** `/api/login`

**Response includes:**
- `expires_in`: Token lifetime in seconds

### 3. Refresh Token ⭐ NEW

**POST** `/api/refresh`

**Headers:**
```
Authorization: Bearer {your_current_token}
Accept: application/json
```

**Response (200):**
```json
{
  "message": "Token refreshed successfully",
  "access_token": "2|yyyyyyyyyyyyyyyyyyyyyyyyyyyy",
  "token_type": "Bearer",
  "expires_in": 3600
}
```

**What happens:**
1. Your current token is revoked
2. A new token is generated with fresh expiration time
3. You receive the new token in the response

**Error Response (401):**
```json
{
  "message": "Token has expired. Please refresh your token.",
  "error": "token_expired"
}
```

### 4. Logout

**POST** `/api/logout`

Revokes your current token permanently.

## Usage Examples

### cURL Example

```bash
# Login first
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"john@example.com","password":"password123"}'

# Response: { "access_token": "OLD_TOKEN", "expires_in": 3600 }

# Refresh token before it expires
curl -X POST http://127.0.0.1:8000/api/refresh \
  -H "Authorization: Bearer OLD_TOKEN" \
  -H "Accept: application/json"

# Response: { "access_token": "NEW_TOKEN", "expires_in": 3600 }

# Use new token for subsequent requests
curl -X GET http://127.0.0.1:8000/api/user \
  -H "Authorization: Bearer NEW_TOKEN" \
  -H "Accept: application/json"
```

### JavaScript Example

```javascript
let accessToken = null;
let tokenExpiresIn = null;

// Login
async function login(email, password) {
  const response = await fetch('http://127.0.0.1:8000/api/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: JSON.stringify({ email, password })
  });

  const data = await response.json();

  if (response.ok) {
    accessToken = data.access_token;
    tokenExpiresIn = data.expires_in; // seconds

    // Set up auto-refresh before expiration
    scheduleTokenRefresh(tokenExpiresIn);
  }

  return data;
}

// Refresh token
async function refreshToken() {
  const response = await fetch('http://127.0.0.1:8000/api/refresh', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${accessToken}`,
      'Accept': 'application/json',
    }
  });

  const data = await response.json();

  if (response.ok) {
    accessToken = data.access_token;
    tokenExpiresIn = data.expires_in;

    // Schedule next refresh
    scheduleTokenRefresh(tokenExpiresIn);
  } else if (data.error === 'token_expired') {
    // Token expired, redirect to login
    window.location.href = '/login';
  }

  return data;
}

// Auto-refresh token before expiration
function scheduleTokenRefresh(expiresIn) {
  // Refresh 5 minutes before expiration
  const refreshTime = (expiresIn - 300) * 1000; // Convert to milliseconds

  setTimeout(async () => {
    await refreshToken();
  }, refreshTime);
}

// Make authenticated API call with auto-retry on token expiration
async function apiCall(url, options = {}) {
  const response = await fetch(url, {
    ...options,
    headers: {
      ...options.headers,
      'Authorization': `Bearer ${accessToken}`,
      'Accept': 'application/json',
    }
  });

  // If token expired, try to refresh and retry once
  if (response.status === 401) {
    const errorData = await response.json();

    if (errorData.error === 'token_expired') {
      const refreshResult = await refreshToken();

      if (refreshResult.access_token) {
        // Retry original request with new token
        return fetch(url, {
          ...options,
          headers: {
            ...options.headers,
            'Authorization': `Bearer ${accessToken}`,
            'Accept': 'application/json',
          }
        });
      }
    }
  }

  return response;
}

// Example usage
async function getUserData() {
  const response = await apiCall('http://127.0.0.1:8000/api/user');
  const data = await response.json();
  return data;
}
```

### React/Vue Example with Axios

```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  headers: {
    'Accept': 'application/json',
  }
});

let refreshTimer = null;

// Add token to requests
api.interceptors.request.use(config => {
  const token = localStorage.getItem('access_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Handle token expiration
api.interceptors.response.use(
  response => response,
  async error => {
    if (error.response?.status === 401 &&
        error.response?.data?.error === 'token_expired') {

      try {
        // Try to refresh token
        const response = await api.post('/refresh');

        // Save new token
        localStorage.setItem('access_token', response.data.access_token);
        localStorage.setItem('expires_in', response.data.expires_in);

        // Schedule next refresh
        scheduleRefresh(response.data.expires_in);

        // Retry original request
        error.config.headers.Authorization = `Bearer ${response.data.access_token}`;
        return api.request(error.config);

      } catch (refreshError) {
        // Refresh failed, redirect to login
        localStorage.removeItem('access_token');
        window.location.href = '/login';
        return Promise.reject(refreshError);
      }
    }

    return Promise.reject(error);
  }
);

// Schedule automatic token refresh
function scheduleRefresh(expiresIn) {
  if (refreshTimer) clearTimeout(refreshTimer);

  // Refresh 5 minutes before expiration
  const refreshTime = (expiresIn - 300) * 1000;

  refreshTimer = setTimeout(async () => {
    try {
      const response = await api.post('/refresh');
      localStorage.setItem('access_token', response.data.access_token);
      localStorage.setItem('expires_in', response.data.expires_in);
      scheduleRefresh(response.data.expires_in);
    } catch (error) {
      console.error('Auto-refresh failed:', error);
      window.location.href = '/login';
    }
  }, refreshTime);
}

// Login
async function login(email, password) {
  const response = await api.post('/login', { email, password });
  localStorage.setItem('access_token', response.data.access_token);
  localStorage.setItem('expires_in', response.data.expires_in);
  scheduleRefresh(response.data.expires_in);
  return response.data;
}

export { api, login };
```

## Best Practices

### 1. Proactive Token Refresh

**Don't wait** for the token to expire. Refresh it proactively:

```javascript
// ✅ Good: Refresh 5-10 minutes before expiration
const refreshTime = (expiresIn - 300) * 1000;

// ❌ Bad: Wait until token expires
const refreshTime = expiresIn * 1000;
```

### 2. Store Token Securely

```javascript
// ✅ Good: Store in memory or httpOnly cookie (for web apps)
let token = null; // In-memory

// ⚠️ Acceptable for SPAs: localStorage (but vulnerable to XSS)
localStorage.setItem('access_token', token);

// ❌ Bad: Store in regular cookies without httpOnly flag
document.cookie = `token=${token}`;
```

### 3. Handle Refresh Failures

```javascript
// ✅ Good: Graceful fallback
try {
  await refreshToken();
} catch (error) {
  // Redirect to login
  window.location.href = '/login';
}

// ❌ Bad: Silent failure
try {
  await refreshToken();
} catch (error) {
  // Do nothing
}
```

### 4. Single Refresh Request

Prevent multiple simultaneous refresh requests:

```javascript
let isRefreshing = false;
let refreshSubscribers = [];

async function refreshToken() {
  if (isRefreshing) {
    // Return a promise that resolves when refresh completes
    return new Promise(resolve => {
      refreshSubscribers.push(resolve);
    });
  }

  isRefreshing = true;

  try {
    const response = await api.post('/refresh');
    const newToken = response.data.access_token;

    // Notify all waiting requests
    refreshSubscribers.forEach(callback => callback(newToken));
    refreshSubscribers = [];

    return newToken;
  } finally {
    isRefreshing = false;
  }
}
```

## Token Lifecycle

```
1. Login/Register
   ↓
2. Receive token with expires_in (3600 seconds)
   ↓
3. Schedule auto-refresh at 55 minutes (3300 seconds)
   ↓
4. Continue using token for API calls
   ↓
5. Auto-refresh triggered at 55 minutes
   ↓
6. Old token revoked, new token issued
   ↓
7. Update stored token
   ↓
8. Repeat from step 3
   ↓
9. User logout → Token permanently revoked
```

## Troubleshooting

### Token Expired Error

**Error:**
```json
{
  "message": "Token has expired. Please refresh your token.",
  "error": "token_expired"
}
```

**Solutions:**
1. Call `/api/refresh` endpoint
2. If refresh fails, redirect user to login
3. Implement auto-refresh to prevent expiration

### Refresh Token Already Expired

If your token is already expired, you cannot refresh it. User must login again.

### Multiple Devices

Each device gets its own token. Refreshing on one device doesn't affect tokens on other devices.

## Configuration Options

### Change Expiration Time

In `.env`:
```env
# 15 minutes
SANCTUM_TOKEN_EXPIRATION=15

# 4 hours
SANCTUM_TOKEN_EXPIRATION=240

# 24 hours
SANCTUM_TOKEN_EXPIRATION=1440

# 7 days
SANCTUM_TOKEN_EXPIRATION=10080
```

### Disable Expiration

In `config/sanctum.php`:
```php
'expiration' => null, // Tokens never expire
```

⚠️ **Not recommended for production** - always use token expiration for security.

## Security Considerations

1. **HTTPS Only** - Always use HTTPS in production to prevent token interception
2. **Short Expiration** - Use shorter expiration times (15-60 minutes) for sensitive operations
3. **Refresh Before Expiration** - Implement auto-refresh to maintain user sessions
4. **Secure Storage** - Never expose tokens in URLs or console logs
5. **Revoke on Logout** - Always revoke tokens when user logs out
6. **Monitor Failed Refreshes** - Track failed refresh attempts for security monitoring

## Summary

- ✅ Tokens expire after 60 minutes by default
- ✅ Use `/api/refresh` to get a new token before expiration
- ✅ Old token is revoked when refreshing
- ✅ Implement auto-refresh in your frontend for seamless UX
- ✅ Handle refresh failures gracefully by redirecting to login
- ✅ Configure expiration time via `SANCTUM_TOKEN_EXPIRATION` env variable

For more information, see the main [README.md](README.md).
