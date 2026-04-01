# Chat Frontend — React

Implementation guide for consuming the Direktory chat API in a React app.

---

## Install

```bash
npm install pusher-js axios
```

---

## Environment Variables

```env
REACT_APP_API_URL=https://your-api.com/api
REACT_APP_PUSHER_APP_KEY=direktory-key
REACT_APP_PUSHER_HOST=your-soketi-host
REACT_APP_PUSHER_PORT=6001
REACT_APP_PUSHER_TLS=true
```

> **Never put secrets (app secret, database credentials) in frontend env vars.** Only the public `PUSHER_APP_KEY` is safe here.

---

## How the React App and Laravel API Actually Connect

There are **three separate connections** happening. Understanding each one is important:

```
React App (browser)
    │
    ├── 1. REST (HTTP)  ──────────────────▶  Laravel API  (port 8000)
    │         axios calls /api/*                  │
    │                                             │ broadcasts event
    ├── 2. Channel Auth (HTTP) ────────────▶  Laravel /broadcasting/auth
    │         POST with Bearer token              │
    │                                             │ tells Soketi "allowed"
    └── 3. WebSocket (WS) ──────────────────▶  Soketi  (port 6001)
              subscribe to private channel        │
              receive pushed messages  ◀──────────┘
```

### Connection 1 — REST API (HTTP)

The React app talks to Laravel directly over HTTP. Both run in Docker, but the React app is a **separate frontend** outside Docker — it hits the API via the exposed port.

**Local dev:**
```
React app running at:   http://localhost:3000
Laravel API exposed at: http://localhost:8000
```

React env:
```env
REACT_APP_API_URL=http://localhost:8000/api
```

Laravel CORS must allow the React origin. It's already in `.env.example`:
```env
CORS_ALLOWED_ORIGINS=https://workers-directory.vercel.app,http://localhost:3000
```

---

### Connection 2 — Channel Authorization (HTTP)

When React subscribes to a private channel, Pusher.js automatically calls `/broadcasting/auth` on Laravel **before** the WebSocket connection is allowed. This is what proves "this user owns this channel".

```
React (pusher-js)
  │
  │  POST http://localhost:8000/broadcasting/auth
  │  Authorization: Bearer <sanctum_token>
  │  Body: { socket_id: "...", channel_name: "private-chat.user.2" }
  │
  ▼
Laravel → routes/channels.php
  → checks token via auth:sanctum
  → checks user owns the channel
  → returns { auth: "..." } signed response
  │
  ▼
Soketi accepts the subscription
```

This is why the `authorizer` in `pusher.js` hits `http://localhost:8000/broadcasting/auth`, not Soketi directly.

---

### Connection 3 — WebSocket (WS)

After authorization, the browser opens a persistent WebSocket connection **directly to Soketi**, not to Laravel. Laravel only writes to Soketi via the internal Docker network when it fires a broadcast event.

```
React (browser)
  │
  │  ws://localhost:6001  (local dev)
  │  wss://soketi.yourserver.com  (production)
  │
  ▼
Soketi (port 6001, inside Docker)
  ▲
  │  internal push (via Pusher PHP SDK over Docker network)
  │  host: soketi  (Docker service name, not localhost)
  │
Laravel (inside Docker)
```

> **Why `PUSHER_HOST=soketi` in Laravel `.env` but `REACT_APP_PUSHER_HOST=localhost` in React `.env`?**
> Laravel runs *inside* Docker — it reaches Soketi via the internal Docker network using the service name `soketi`.
> The React app runs *outside* Docker in the browser — it reaches Soketi via the exposed port on `localhost`.

---

### Full Example: Local Development

**Laravel `.env`** (inside Docker — uses Docker service names):
```env
PUSHER_HOST=soketi
PUSHER_PORT=6001
PUSHER_SCHEME=http
APP_URL=http://localhost:8000
CORS_ALLOWED_ORIGINS=http://localhost:3000
```

**React `.env`** (browser — uses localhost exposed ports):
```env
REACT_APP_API_URL=http://localhost:8000/api
REACT_APP_PUSHER_APP_KEY=direktory-key
REACT_APP_PUSHER_HOST=localhost
REACT_APP_PUSHER_PORT=6001
REACT_APP_PUSHER_TLS=false
```

**Docker ports exposed to host:**
```yaml
# docker-compose.yml
app:
  ports:
    - "8000:80"      # Laravel API → localhost:8000
soketi:
  ports:
    - "6001:6001"    # WebSocket   → localhost:6001
```

---

### Full Example: Production (Separate Domains)

This is the real-world scenario. The React app and the Laravel API are deployed independently on different domains:

```
React app  →  https://workers-directory.vercel.app   (Vercel / any static host)
Laravel API →  https://api.direktory.com              (your server / VPS)
Soketi      →  wss://soketi.direktory.com             (same server, nginx proxied)
```

The browser never knows they're on the same server or different servers — it just calls each domain independently.

---

**Step 1 — Laravel `.env` on the server**

Soketi still uses its Docker service name internally. The public URLs are what Laravel exposes to the outside world.

```env
APP_URL=https://api.direktory.com

# Soketi: Laravel reaches it internally via Docker service name
PUSHER_HOST=soketi
PUSHER_PORT=6001
PUSHER_SCHEME=http

# CORS: allow ONLY your React app's domain — comma-separated, no trailing slash
CORS_ALLOWED_ORIGINS=https://workers-directory.vercel.app
```

Your `config/cors.php` already reads this correctly:
```php
'allowed_origins' => array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', '*'))),
```

Also make sure `/broadcasting/auth` is covered by CORS. Add it to `paths`:
```php
'paths' => ['api/*', 'sanctum/csrf-cookie', 'broadcasting/auth'],
```

---

**Step 2 — Expose Soketi publicly via nginx**

The browser needs to reach Soketi over `wss://` (secure WebSocket). You do this by proxying it through nginx on the same server. Soketi never needs its own domain — just a path or subdomain.

```nginx
# /etc/nginx/sites-available/soketi.direktory.com
server {
    listen 443 ssl;
    server_name soketi.direktory.com;

    ssl_certificate     /etc/letsencrypt/live/soketi.direktory.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/soketi.direktory.com/privkey.pem;

    location / {
        proxy_pass http://127.0.0.1:6001;
        proxy_http_version 1.1;

        # Required for WebSocket upgrade
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_read_timeout 3600s;  # keep WS connections alive
    }
}
```

> Get a free SSL cert: `certbot --nginx -d soketi.direktory.com`

---

**Step 3 — React `.env` (on Vercel or any static host)**

The browser points to the public hostnames — never to `localhost` or internal Docker names.

```env
REACT_APP_API_URL=https://api.direktory.com/api
REACT_APP_PUSHER_APP_KEY=direktory-key
REACT_APP_PUSHER_HOST=soketi.direktory.com
REACT_APP_PUSHER_PORT=443
REACT_APP_PUSHER_TLS=true
```

On Vercel, set these in **Project Settings → Environment Variables** so they're baked into the build. They are not secrets — they're public config embedded in the JS bundle.

---

**Step 4 — What happens at runtime**

```
Browser (workers-directory.vercel.app)
  │
  │  1. POST https://api.direktory.com/api/login
  │     ← receives Bearer token
  │
  │  2. GET https://api.direktory.com/api/conversations
  │     Authorization: Bearer <token>
  │     ← CORS header: Access-Control-Allow-Origin: https://workers-directory.vercel.app
  │     ← conversation list
  │
  │  3. POST https://api.direktory.com/broadcasting/auth
  │     Authorization: Bearer <token>
  │     Body: { socket_id, channel_name: "private-chat.user.2" }
  │     ← Laravel verifies token → returns signed Pusher auth
  │
  │  4. wss://soketi.direktory.com  (WebSocket upgrade via nginx)
  │     ← persistent connection open
  │
  │  5. Other user sends a message
  │     POST https://api.direktory.com/api/messages
  │     → Laravel saves message
  │     → Laravel broadcasts MessageSent event to Soketi (internally via soketi:6001)
  │     → Soketi pushes to browser over existing wss:// connection
  │     ← 'message.sent' event fires in browser instantly
```

---

**Step 5 — docker-compose.yml on the server**

In production you don't need to expose port `6001` publicly — nginx handles that. Keep it internal:

```yaml
soketi:
  image: quay.io/soketi/soketi:latest-16-alpine
  # No ports: block — nginx proxies to 127.0.0.1:6001 on the host
  environment:
    SOKETI_DEFAULT_APP_ID: ${PUSHER_APP_ID}
    SOKETI_DEFAULT_APP_KEY: ${PUSHER_APP_KEY}
    SOKETI_DEFAULT_APP_SECRET: ${PUSHER_APP_SECRET}
  networks:
    - laravel

app:
  ports:
    - "8000:80"   # nginx or a load balancer forwards :443 → :8000
  networks:
    - laravel
```

Or expose `6001` only to `127.0.0.1` so it's not accessible from the internet directly:
```yaml
soketi:
  ports:
    - "127.0.0.1:6001:6001"   # only reachable locally — nginx proxies it
```

---

### Summary Table

| | Local Dev | Production (separate domains) |
|--|-----------|-------------------------------|
| React hosted at | `http://localhost:3000` | `https://workers-directory.vercel.app` |
| Laravel API | `http://localhost:8000/api` | `https://api.direktory.com/api` |
| Channel auth | `http://localhost:8000/broadcasting/auth` | `https://api.direktory.com/broadcasting/auth` |
| WebSocket | `ws://localhost:6001` | `wss://soketi.direktory.com` (nginx proxy) |
| Laravel → Soketi | `http://soketi:6001` (Docker) | `http://soketi:6001` (Docker, same network) |
| CORS origin | `http://localhost:3000` | `https://workers-directory.vercel.app` |

> **The key rule:** Laravel always talks to Soketi via the internal Docker service name `soketi`. The browser always talks to Soketi via the public nginx-proxied `wss://` URL. These are two different paths to the same Soketi container.

---

## Security Notes

| Concern | Approach used |
|---------|--------------|
| Token storage | `sessionStorage` instead of `localStorage` — not persisted across tabs, cleared on tab close, reduces XSS exposure window |
| XSS in messages | All user content rendered as text nodes, never via `innerHTML` or `dangerouslySetInnerHTML` |
| Token expiry | Axios 401 interceptor catches expired tokens and redirects to login |
| TLS | `forceTLS` driven by env var — always `true` in production |
| Pusher auth header | Read fresh from `sessionStorage` per auth request, not captured at init |
| Input length | Client-side max enforced before sending (server also validates at 5000 chars) |

---

## Files

### `src/lib/auth.js`

Single source of truth for token read/write. Using `sessionStorage` reduces the XSS token-theft window compared to `localStorage` — a stolen token only lives for the current browser tab session.

```js
// src/lib/auth.js
const KEY = 'auth_token';

export const auth = {
  setToken(token) {
    sessionStorage.setItem(KEY, token);
  },
  getToken() {
    return sessionStorage.getItem(KEY);
  },
  clearToken() {
    sessionStorage.removeItem(KEY);
  },
  isAuthenticated() {
    return !!sessionStorage.getItem(KEY);
  },
};
```

---

### `src/lib/api.js`

Axios instance with:
- Token attached per request (read fresh each time, handles rotation)
- 401 interceptor that clears the session and redirects to login

```js
// src/lib/api.js
import axios from 'axios';
import { auth } from './auth';

const api = axios.create({
  baseURL: process.env.REACT_APP_API_URL,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
});

// Attach token on every request (read fresh — handles token rotation)
api.interceptors.request.use((config) => {
  const token = auth.getToken();
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Handle token expiry globally
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      auth.clearToken();
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default api;
```

---

### `src/lib/pusher.js`

Pusher singleton. Key points:
- `forceTLS` reads from env — `true` in production, `false` only for local dev
- Auth header reads the token fresh on every channel auth request via `authDecorator`
- `disconnectPusher` must be called on logout to prevent stale authenticated connections

```js
// src/lib/pusher.js
import Pusher from 'pusher-js';
import { auth } from './auth';

let instance = null;

export function getPusher() {
  if (instance) return instance;

  const useTLS = process.env.REACT_APP_PUSHER_TLS === 'true';

  instance = new Pusher(process.env.REACT_APP_PUSHER_APP_KEY, {
    wsHost: process.env.REACT_APP_PUSHER_HOST,
    wsPort: Number(process.env.REACT_APP_PUSHER_PORT),
    wssPort: Number(process.env.REACT_APP_PUSHER_PORT),
    forceTLS: useTLS,
    enabledTransports: [useTLS ? 'wss' : 'ws'],
    cluster: 'mt1',
    // authDecorator lets us read the token fresh per auth request
    // instead of capturing it once at construction time
    authEndpoint: `${process.env.REACT_APP_API_URL.replace('/api', '')}/broadcasting/auth`,
    auth: {
      headers: {},
    },
    authorizer: (channel) => ({
      authorize: (socketId, callback) => {
        const token = auth.getToken();
        if (!token) {
          callback(new Error('No auth token'), null);
          return;
        }

        fetch(`${process.env.REACT_APP_API_URL.replace('/api', '')}/broadcasting/auth`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            Authorization: `Bearer ${token}`,
          },
          body: JSON.stringify({
            socket_id: socketId,
            channel_name: channel.name,
          }),
        })
          .then((res) => {
            if (!res.ok) throw new Error('Channel auth failed');
            return res.json();
          })
          .then((data) => callback(null, data))
          .catch((err) => callback(err, null));
      },
    }),
  });

  return instance;
}

export function disconnectPusher() {
  if (instance) {
    instance.disconnect();
    instance = null;
  }
}
```

---

### `src/context/AuthContext.js`

Provides `user`, `activeProfileId`, and `logout` to the whole app. This avoids hardcoding IDs anywhere.

```js
// src/context/AuthContext.js
import { createContext, useContext, useState, useCallback } from 'react';
import { auth } from '../lib/auth';
import { disconnectPusher } from '../lib/pusher';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  // Initialize from sessionStorage so a page refresh keeps the user logged in
  const [user, setUser] = useState(() => {
    try {
      const raw = sessionStorage.getItem('auth_user');
      return raw ? JSON.parse(raw) : null;
    } catch {
      return null;
    }
  });

  const [activeProfileId, setActiveProfileId] = useState(() => {
    const raw = sessionStorage.getItem('active_profile_id');
    return raw ? Number(raw) : null;
  });

  const login = useCallback((token, userData, profileId = null) => {
    auth.setToken(token);
    sessionStorage.setItem('auth_user', JSON.stringify(userData));
    if (profileId) sessionStorage.setItem('active_profile_id', String(profileId));
    setUser(userData);
    setActiveProfileId(profileId);
  }, []);

  const logout = useCallback(() => {
    auth.clearToken();
    sessionStorage.removeItem('auth_user');
    sessionStorage.removeItem('active_profile_id');
    disconnectPusher(); // close WebSocket on logout
    setUser(null);
    setActiveProfileId(null);
  }, []);

  return (
    <AuthContext.Provider value={{ user, activeProfileId, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
}

export const useAuth = () => useContext(AuthContext);
```

---

### `src/hooks/useConversations.js`

```js
// src/hooks/useConversations.js
import { useState, useEffect, useCallback } from 'react';
import api from '../lib/api';

export function useConversations() {
  const [conversations, setConversations] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const fetchConversations = useCallback(async () => {
    setError(null);
    try {
      const res = await api.get('/conversations');
      setConversations(res.data.data);
    } catch (err) {
      setError('Failed to load conversations.');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchConversations();
  }, [fetchConversations]);

  return { conversations, loading, error, refetch: fetchConversations };
}
```

---

### `src/hooks/useChat.js`

```js
// src/hooks/useChat.js
import { useState, useEffect, useRef, useCallback } from 'react';
import api from '../lib/api';
import { getPusher } from '../lib/pusher';

const MAX_MESSAGE_LENGTH = 5000;

export function useChat({ profileId, bookingId = null, currentUserId, activeProfileId = null }) {
  const [messages, setMessages] = useState([]);
  const [conversation, setConversation] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [sending, setSending] = useState(false);
  const channelRef = useRef(null);

  // Load conversation + message history
  useEffect(() => {
    if (!profileId) return;

    let cancelled = false;

    const params = { profile_id: profileId };
    if (bookingId) params.booking_id = bookingId;
    if (activeProfileId) params.user_id = currentUserId;

    setLoading(true);
    setError(null);

    api.get('/messages', { params })
      .then((res) => {
        if (cancelled) return;
        setConversation(res.data.data.conversation);
        setMessages(res.data.data.messages);
      })
      .catch(() => {
        if (!cancelled) setError('Failed to load messages.');
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });

    // Cleanup: if profileId changes mid-flight, discard stale response
    return () => { cancelled = true; };
  }, [profileId, bookingId, currentUserId, activeProfileId]);

  // Subscribe to real-time channel
  useEffect(() => {
    if (!conversation) return;

    const pusher = getPusher();
    const channelName = activeProfileId
      ? `private-chat.profile.${activeProfileId}`
      : `private-chat.user.${currentUserId}`;

    const channel = pusher.subscribe(channelName);
    channelRef.current = channel;

    const handleMessage = (data) => {
      if (data.conversation_id === conversation.id) {
        setMessages((prev) => [...prev, data]);
      }
    };

    channel.bind('message.sent', handleMessage);

    return () => {
      channel.unbind('message.sent', handleMessage);
      pusher.unsubscribe(channelName);
      channelRef.current = null;
    };
  }, [conversation?.id, currentUserId, activeProfileId]);

  const sendMessage = useCallback(async (body) => {
    if (!conversation || !body.trim() || sending) return;

    const trimmed = body.trim();
    if (trimmed.length > MAX_MESSAGE_LENGTH) {
      throw new Error(`Message exceeds ${MAX_MESSAGE_LENGTH} characters.`);
    }

    setSending(true);
    try {
      const res = await api.post('/messages', {
        conversation_id: conversation.id,
        body: trimmed,
      });
      setMessages((prev) => [...prev, res.data.data]);
    } finally {
      setSending(false);
    }
  }, [conversation, sending]);

  const markRead = useCallback(async () => {
    if (!conversation) return;
    await api.patch(`/conversations/${conversation.id}/read`).catch(() => {
      // Non-critical — don't surface this error to the user
    });
  }, [conversation]);

  return { messages, conversation, loading, error, sending, sendMessage, markRead };
}
```

---

### `src/components/ChatWindow.jsx`

Message bodies rendered as text nodes via JSX — React escapes them automatically, preventing XSS.

```jsx
// src/components/ChatWindow.jsx
import { useState, useEffect, useRef } from 'react';
import { useChat } from '../hooks/useChat';
import { useAuth } from '../context/AuthContext';

const MAX_LENGTH = 5000;

export default function ChatWindow({ profileId, bookingId }) {
  const { user, activeProfileId } = useAuth();
  const { messages, loading, error, sending, sendMessage, markRead } = useChat({
    profileId,
    bookingId,
    currentUserId: user?.id,
    activeProfileId,
  });

  const [input, setInput] = useState('');
  const [sendError, setSendError] = useState(null);
  const bottomRef = useRef(null);

  useEffect(() => {
    bottomRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages]);

  useEffect(() => {
    markRead();
  }, [markRead]);

  const handleSend = async (e) => {
    e.preventDefault();
    setSendError(null);
    try {
      await sendMessage(input);
      setInput('');
    } catch (err) {
      setSendError(err.message);
    }
  };

  if (loading) return <div>Loading messages...</div>;
  if (error) return <div style={{ color: 'red' }}>{error}</div>;

  return (
    <div style={{ display: 'flex', flexDirection: 'column', height: '100%' }}>
      <div style={{ flex: 1, overflowY: 'auto', padding: '1rem' }}>
        {messages.map((msg) => {
          const isMine = activeProfileId
            ? msg.sender_profile_id === activeProfileId
            : msg.sender_user_id === user?.id && !msg.sender_profile_id;

          return (
            <div
              key={msg.id}
              style={{
                display: 'flex',
                justifyContent: isMine ? 'flex-end' : 'flex-start',
                marginBottom: '0.5rem',
              }}
            >
              <div
                style={{
                  maxWidth: '70%',
                  padding: '0.5rem 0.75rem',
                  borderRadius: '1rem',
                  background: isMine ? '#2563eb' : '#e5e7eb',
                  color: isMine ? '#fff' : '#111',
                }}
              >
                {/* JSX renders msg.body as a text node — XSS safe */}
                <p style={{ margin: 0 }}>{msg.body}</p>
                <small style={{ opacity: 0.6, fontSize: '0.7rem' }}>
                  {new Date(msg.created_at).toLocaleTimeString()}
                </small>
              </div>
            </div>
          );
        })}
        <div ref={bottomRef} />
      </div>

      {sendError && <p style={{ color: 'red', padding: '0 0.75rem' }}>{sendError}</p>}

      <form onSubmit={handleSend} style={{ display: 'flex', padding: '0.75rem', borderTop: '1px solid #e5e7eb', gap: '0.5rem' }}>
        <input
          value={input}
          onChange={(e) => setInput(e.target.value.slice(0, MAX_LENGTH))}
          placeholder="Type a message..."
          maxLength={MAX_LENGTH}
          disabled={sending}
          style={{ flex: 1, padding: '0.5rem', borderRadius: '0.5rem', border: '1px solid #d1d5db' }}
        />
        <button type="submit" disabled={!input.trim() || sending} style={{ padding: '0.5rem 1rem' }}>
          {sending ? '...' : 'Send'}
        </button>
      </form>
    </div>
  );
}
```

---

### `src/components/ConversationList.jsx`

```jsx
// src/components/ConversationList.jsx
import { useConversations } from '../hooks/useConversations';

export default function ConversationList({ onSelect, activeId }) {
  const { conversations, loading, error } = useConversations();

  if (loading) return <div>Loading...</div>;
  if (error) return <div style={{ color: 'red' }}>{error}</div>;
  if (!conversations.length) return <div style={{ padding: '1rem', color: '#9ca3af' }}>No conversations yet.</div>;

  return (
    <ul style={{ listStyle: 'none', padding: 0, margin: 0 }}>
      {conversations.map((conv) => (
        <li
          key={conv.id}
          onClick={() => onSelect(conv)}
          style={{
            padding: '0.75rem 1rem',
            cursor: 'pointer',
            background: conv.id === activeId ? '#eff6ff' : 'transparent',
            borderBottom: '1px solid #f3f4f6',
          }}
        >
          <div style={{ fontWeight: 600, display: 'flex', justifyContent: 'space-between' }}>
            {/* Text node — XSS safe */}
            {conv.profile?.name ?? `Profile #${conv.profile_id}`}
            {conv.unread_count > 0 && (
              <span style={{ background: '#2563eb', color: '#fff', borderRadius: '9999px', padding: '0 0.4rem', fontSize: '0.75rem' }}>
                {conv.unread_count}
              </span>
            )}
          </div>
          <div style={{ fontSize: '0.85rem', color: '#6b7280', marginTop: '0.2rem' }}>
            {conv.latest_message?.body ?? 'No messages yet'}
          </div>
        </li>
      ))}
    </ul>
  );
}
```

---

### `src/pages/ChatPage.jsx`

`currentUserId` and `activeProfileId` come from `AuthContext` — never hardcoded.

```jsx
// src/pages/ChatPage.jsx
import { useState } from 'react';
import ConversationList from '../components/ConversationList';
import ChatWindow from '../components/ChatWindow';
import { useAuth } from '../context/AuthContext';

export default function ChatPage() {
  const { user, logout } = useAuth();
  const [activeConversation, setActiveConversation] = useState(null);

  if (!user) {
    window.location.href = '/login';
    return null;
  }

  return (
    <div style={{ display: 'flex', height: '100vh' }}>
      <div style={{ width: '300px', borderRight: '1px solid #e5e7eb', overflowY: 'auto' }}>
        <div style={{ padding: '0.75rem 1rem', borderBottom: '1px solid #e5e7eb', display: 'flex', justifyContent: 'space-between' }}>
          <strong>Messages</strong>
          <button onClick={logout} style={{ fontSize: '0.8rem', color: '#6b7280', background: 'none', border: 'none', cursor: 'pointer' }}>
            Logout
          </button>
        </div>
        <ConversationList
          onSelect={setActiveConversation}
          activeId={activeConversation?.id}
        />
      </div>

      <div style={{ flex: 1 }}>
        {activeConversation ? (
          <ChatWindow
            profileId={activeConversation.profile_id}
            bookingId={activeConversation.booking_id}
          />
        ) : (
          <div style={{ padding: '2rem', color: '#9ca3af' }}>Select a conversation</div>
        )}
      </div>
    </div>
  );
}
```

---

### `src/index.js` / `src/main.jsx`

Wrap the app in `AuthProvider`:

```jsx
import React from 'react';
import ReactDOM from 'react-dom/client';
import { AuthProvider } from './context/AuthContext';
import App from './App';

ReactDOM.createRoot(document.getElementById('root')).render(
  <React.StrictMode>
    <AuthProvider>
      <App />
    </AuthProvider>
  </React.StrictMode>
);
```

---

## Flow Diagram

```
App mounts
  └─ AuthProvider reads user/token from sessionStorage

User opens ChatPage (guard: redirect to /login if no user)
  └─ ConversationList → GET /api/conversations

User selects a conversation
  └─ ChatWindow mounts
       ├─ GET /api/messages?profile_id=X  (load history)
       ├─ PATCH /api/conversations/{id}/read
       └─ getPusher().subscribe('private-chat.user.{id}')
            └─ authorizer() → POST /broadcasting/auth (fresh token each time)
            └─ bind('message.sent') → append to messages

User sends a message
  └─ POST /api/messages (client validates length first)
  └─ Optimistic append to state

Other participant sends
  └─ Soketi pushes 'message.sent' → append to messages instantly

User logs out
  └─ auth.clearToken() + sessionStorage cleared + disconnectPusher()
```
