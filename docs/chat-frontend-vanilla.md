# Chat Frontend — Vanilla ES6

Implementation guide for consuming the Direktory chat API with plain ES6 — no framework required.

---

## Install

Via npm (recommended — use a bundler like Vite):

```bash
npm install pusher-js
```

Via CDN with Subresource Integrity (SRI) — required if loading from CDN to prevent supply chain attacks:

```html
<script
  src="https://js.pusher.com/8.2.0/pusher.min.js"
  integrity="sha384-cXWaJJ0IbDqfHpMIJxQ6o0XQKZP9XSxMYGWCHlLzE2OqzJ4YFHpOv5cHvQC4VQ="
  crossorigin="anonymous"
></script>
```

> Get the correct SRI hash for the version you use from [srihash.org](https://www.srihash.org) or your own hosted copy.

---

## Security Notes

| Concern | Approach used |
|---------|--------------|
| Token storage | `sessionStorage` — not persisted across tabs, cleared on tab close, reduces XSS exposure window vs `localStorage` |
| XSS in messages | All message content inserted as `textContent`, never `innerHTML` |
| XSS in conversation names | `_escape()` used when injecting into `innerHTML` template literals |
| Token expiry | `api.js` checks for 401 responses and clears the session |
| TLS | `forceTLS` driven by `CONFIG.useTLS` — always `true` in production |
| Pusher auth token | Read fresh from `sessionStorage` on every channel auth request |
| Input length | Client enforces max before sending (server also validates) |
| CDN integrity | SRI hash required on `<script>` tag |
| Config values | Pushed from server-rendered meta tags, not hardcoded in source |

---

## Injecting Config Safely

Never hardcode API keys in JS source files — they end up in version control. Instead, have your server render them into meta tags:

**Server side (Laravel Blade):**
```html
<meta name="pusher-key" content="{{ config('broadcasting.connections.soketi.key') }}">
<meta name="api-url" content="{{ config('app.url') }}/api">
<meta name="pusher-host" content="{{ config('broadcasting.connections.soketi.options.host') }}">
<meta name="pusher-port" content="{{ config('broadcasting.connections.soketi.options.port') }}">
<meta name="pusher-tls" content="{{ config('broadcasting.connections.soketi.options.scheme') === 'https' ? 'true' : 'false' }}">
```

**Client side (`config.js`):**
```js
// config.js
function getMeta(name) {
  const el = document.querySelector(`meta[name="${name}"]`);
  if (!el) throw new Error(`Missing meta tag: ${name}`);
  return el.getAttribute('content');
}

export const CONFIG = {
  apiUrl: getMeta('api-url'),
  pusherKey: getMeta('pusher-key'),
  pusherHost: getMeta('pusher-host'),
  pusherPort: Number(getMeta('pusher-port')),
  useTLS: getMeta('pusher-tls') === 'true',
};
```

If you are using a bundler (Vite/Webpack), use `.env` files instead:

```js
// config.js (bundler variant)
export const CONFIG = {
  apiUrl: import.meta.env.VITE_API_URL,
  pusherKey: import.meta.env.VITE_PUSHER_APP_KEY,
  pusherHost: import.meta.env.VITE_PUSHER_HOST,
  pusherPort: Number(import.meta.env.VITE_PUSHER_PORT),
  useTLS: import.meta.env.VITE_PUSHER_TLS === 'true',
};
```

---

## Files

### `auth.js`

Single source of truth for token storage. `sessionStorage` is used instead of `localStorage` — tokens are not persisted to disk and are cleared when the tab closes, reducing the window of token theft via XSS.

```js
// auth.js
const TOKEN_KEY = 'auth_token';
const USER_KEY = 'auth_user';

export const auth = {
  setSession(token, user) {
    sessionStorage.setItem(TOKEN_KEY, token);
    sessionStorage.setItem(USER_KEY, JSON.stringify(user));
  },
  getToken() {
    return sessionStorage.getItem(TOKEN_KEY);
  },
  getUser() {
    try {
      return JSON.parse(sessionStorage.getItem(USER_KEY));
    } catch {
      return null;
    }
  },
  clear() {
    sessionStorage.removeItem(TOKEN_KEY);
    sessionStorage.removeItem(USER_KEY);
  },
  isAuthenticated() {
    return !!sessionStorage.getItem(TOKEN_KEY);
  },
};
```

---

### `api.js`

`fetch` wrapper that:
- Attaches a fresh token on every request
- Handles 401 (clears session, redirects to login)
- Throws a typed error for non-2xx responses

```js
// api.js
import { CONFIG } from './config.js';
import { auth } from './auth.js';

async function request(method, path, body = null) {
  const token = auth.getToken();

  const options = {
    method,
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
  };

  if (body) {
    options.body = JSON.stringify(body);
  }

  const res = await fetch(`${CONFIG.apiUrl}${path}`, options);

  if (res.status === 401) {
    auth.clear();
    window.location.href = '/login';
    return;
  }

  if (!res.ok) {
    const err = await res.json().catch(() => ({}));
    throw new Error(err.message ?? `HTTP ${res.status}`);
  }

  return res.json();
}

export const api = {
  get: (path) => request('GET', path),
  post: (path, body) => request('POST', path, body),
  patch: (path, body = null) => request('PATCH', path, body),
};
```

---

### `pusher-client.js`

Singleton Pusher instance. The `authorizer` function is called by Pusher for each private channel subscription — it reads the token fresh each time, so token rotation is handled correctly.

```js
// pusher-client.js
import { CONFIG } from './config.js';
import { auth } from './auth.js';

let pusherInstance = null;

export function getPusher() {
  if (pusherInstance) return pusherInstance;

  pusherInstance = new Pusher(CONFIG.pusherKey, {
    wsHost: CONFIG.pusherHost,
    wsPort: CONFIG.pusherPort,
    wssPort: CONFIG.pusherPort,
    forceTLS: CONFIG.useTLS,
    enabledTransports: [CONFIG.useTLS ? 'wss' : 'ws'],
    cluster: 'mt1',
    // Custom authorizer reads token fresh on every subscription
    authorizer: (channel) => ({
      authorize: (socketId, callback) => {
        const token = auth.getToken();
        if (!token) {
          callback(new Error('Not authenticated'), null);
          return;
        }

        fetch(`${CONFIG.apiUrl.replace('/api', '')}/broadcasting/auth`, {
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

  return pusherInstance;
}

export function disconnectPusher() {
  if (pusherInstance) {
    pusherInstance.disconnect();
    pusherInstance = null;
  }
}
```

---

### `chat.js`

Core chat class. Key security points:
- Message body inserted as `textContent` — not `innerHTML` — preventing XSS
- `_escape()` used only for profile names injected into template literals
- Input capped at `MAX_LENGTH` before the API call
- Double-submit prevented with `_sending` flag

```js
// chat.js
import { api } from './api.js';
import { getPusher } from './pusher-client.js';

const MAX_LENGTH = 5000;

export class Chat {
  constructor({ profileId, bookingId = null, currentUserId, activeProfileId = null, messagesEl, formEl, inputEl }) {
    this.profileId = profileId;
    this.bookingId = bookingId;
    this.currentUserId = currentUserId;
    this.activeProfileId = activeProfileId;
    this.messagesEl = messagesEl;
    this.formEl = formEl;
    this.inputEl = inputEl;

    this.conversation = null;
    this.channel = null;
    this._sending = false;

    this._onSubmit = this._onSubmit.bind(this);
  }

  async init() {
    await this._loadMessages();
    this._subscribeRealtime();
    this._bindForm();
    await this._markRead();
  }

  destroy() {
    this.formEl.removeEventListener('submit', this._onSubmit);

    if (this.channel) {
      const channelName = this.activeProfileId
        ? `private-chat.profile.${this.activeProfileId}`
        : `private-chat.user.${this.currentUserId}`;

      this.channel.unbind('message.sent');
      getPusher().unsubscribe(channelName);
      this.channel = null;
    }
  }

  // ─── Private ──────────────────────────────────────────────

  async _loadMessages() {
    const params = new URLSearchParams({ profile_id: this.profileId });
    if (this.bookingId) params.set('booking_id', this.bookingId);
    if (this.activeProfileId) params.set('user_id', this.currentUserId);

    const data = await api.get(`/messages?${params}`);
    this.conversation = data.data.conversation;

    this.messagesEl.innerHTML = '';
    data.data.messages.forEach((msg) => this._appendMessage(msg));
    this._scrollToBottom();
  }

  _subscribeRealtime() {
    if (!this.conversation) return;

    const channelName = this.activeProfileId
      ? `private-chat.profile.${this.activeProfileId}`
      : `private-chat.user.${this.currentUserId}`;

    const pusher = getPusher();
    this.channel = pusher.subscribe(channelName);

    this.channel.bind('message.sent', (data) => {
      if (data.conversation_id === this.conversation.id) {
        this._appendMessage(data);
        this._scrollToBottom();
      }
    });
  }

  _bindForm() {
    this.formEl.addEventListener('submit', this._onSubmit);
  }

  async _onSubmit(e) {
    e.preventDefault();

    if (this._sending || !this.conversation) return;

    const body = this.inputEl.value.trim();
    if (!body) return;

    if (body.length > MAX_LENGTH) {
      this._showError(`Message cannot exceed ${MAX_LENGTH} characters.`);
      return;
    }

    this._sending = true;
    this.inputEl.value = '';
    this._setSubmitDisabled(true);

    try {
      const data = await api.post('/messages', {
        conversation_id: this.conversation.id,
        body,
      });
      this._appendMessage(data.data);
      this._scrollToBottom();
    } catch (err) {
      this._showError('Failed to send message. Please try again.');
      this.inputEl.value = body; // restore on failure
    } finally {
      this._sending = false;
      this._setSubmitDisabled(false);
    }
  }

  async _markRead() {
    if (!this.conversation) return;
    // Non-critical — swallow errors silently
    await api.patch(`/conversations/${this.conversation.id}/read`).catch(() => {});
  }

  _appendMessage(msg) {
    const isMine = this.activeProfileId
      ? msg.sender_profile_id === this.activeProfileId
      : msg.sender_user_id === this.currentUserId && !msg.sender_profile_id;

    const bubble = document.createElement('div');
    bubble.className = `message__bubble`;

    // Use textContent for message body — prevents XSS
    const bodyEl = document.createElement('p');
    bodyEl.className = 'message__body';
    bodyEl.textContent = msg.body;

    const timeEl = document.createElement('span');
    timeEl.className = 'message__time';
    timeEl.textContent = new Date(msg.created_at).toLocaleTimeString();

    bubble.appendChild(bodyEl);
    bubble.appendChild(timeEl);

    const wrapper = document.createElement('div');
    wrapper.className = `message ${isMine ? 'message--mine' : 'message--theirs'}`;
    wrapper.appendChild(bubble);

    this.messagesEl.appendChild(wrapper);
  }

  _showError(message) {
    let errEl = this.formEl.parentElement.querySelector('.chat-error');
    if (!errEl) {
      errEl = document.createElement('p');
      errEl.className = 'chat-error';
      errEl.style.cssText = 'color:red;padding:0 0.75rem;margin:0';
      this.formEl.before(errEl);
    }
    errEl.textContent = message; // textContent — not innerHTML
    setTimeout(() => errEl.remove(), 4000);
  }

  _setSubmitDisabled(disabled) {
    const btn = this.formEl.querySelector('button[type="submit"]');
    if (btn) btn.disabled = disabled;
  }

  _scrollToBottom() {
    this.messagesEl.scrollTop = this.messagesEl.scrollHeight;
  }

  // Used only for values injected into innerHTML template literals
  _escape(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#x27;');
  }
}
```

---

### `conversations.js`

```js
// conversations.js
import { api } from './api.js';

export class ConversationList {
  constructor(containerEl, onSelect) {
    this.containerEl = containerEl;
    this.onSelect = onSelect;
  }

  async load() {
    try {
      const data = await api.get('/conversations');
      this._render(data.data);
    } catch {
      this.containerEl.textContent = 'Failed to load conversations.';
    }
  }

  _render(conversations) {
    this.containerEl.innerHTML = '';

    if (conversations.length === 0) {
      const p = document.createElement('p');
      p.className = 'no-conversations';
      p.textContent = 'No conversations yet.';
      this.containerEl.appendChild(p);
      return;
    }

    conversations.forEach((conv) => {
      const li = document.createElement('li');
      li.className = 'conversation-item';
      li.dataset.id = conv.id;

      // Profile name — escaped because it goes into innerHTML
      const nameDiv = document.createElement('div');
      nameDiv.className = 'conversation-item__name';
      nameDiv.innerHTML = `
        ${this._escape(conv.profile?.name ?? `Profile #${conv.profile_id}`)}
        ${conv.unread_count > 0 ? `<span class="badge">${Number(conv.unread_count)}</span>` : ''}
      `;

      // Preview — use textContent for user-generated message preview
      const previewDiv = document.createElement('div');
      previewDiv.className = 'conversation-item__preview';
      previewDiv.textContent = conv.latest_message?.body ?? 'No messages yet';

      li.appendChild(nameDiv);
      li.appendChild(previewDiv);

      li.addEventListener('click', () => {
        document.querySelectorAll('.conversation-item').forEach((el) => el.classList.remove('active'));
        li.classList.add('active');
        li.querySelector('.badge')?.remove();
        this.onSelect(conv);
      });

      this.containerEl.appendChild(li);
    });
  }

  _escape(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#x27;');
  }
}
```

---

### `index.html`

Note the SRI hash on the Pusher CDN script and the `Content-Security-Policy` meta tag.

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Direktory Chat</title>

  <!--
    Content Security Policy:
    - default-src 'self'         — only load resources from own origin by default
    - connect-src 'self' wss:    — allow WebSocket connections
    - script-src 'self'          — no inline scripts; only bundled JS
    Adjust to match your actual CDN/API domains in production.
  -->
  <meta http-equiv="Content-Security-Policy"
    content="default-src 'self'; connect-src 'self' ws: wss:; script-src 'self';" />

  <!--
    Config injected by the server — never hardcoded in JS source.
    In Laravel: {{ config('...') }}
  -->
  <meta name="api-url" content="http://localhost:8000/api" />
  <meta name="pusher-key" content="direktory-key" />
  <meta name="pusher-host" content="127.0.0.1" />
  <meta name="pusher-port" content="6001" />
  <meta name="pusher-tls" content="false" />

  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: system-ui, sans-serif; display: flex; height: 100vh; }

    #sidebar { width: 280px; border-right: 1px solid #e5e7eb; overflow-y: auto; display: flex; flex-direction: column; }
    #sidebar h2 { padding: 1rem; font-size: 1rem; border-bottom: 1px solid #e5e7eb; }
    #conversation-list { list-style: none; padding: 0; margin: 0; flex: 1; overflow-y: auto; }
    .conversation-item { padding: 0.75rem 1rem; cursor: pointer; border-bottom: 1px solid #f3f4f6; }
    .conversation-item:hover, .conversation-item.active { background: #eff6ff; }
    .conversation-item__name { font-weight: 600; display: flex; justify-content: space-between; align-items: center; }
    .conversation-item__preview { font-size: 0.85rem; color: #6b7280; margin-top: 0.2rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .badge { background: #2563eb; color: #fff; border-radius: 9999px; padding: 0 0.4rem; font-size: 0.75rem; }
    .no-conversations { padding: 1rem; color: #9ca3af; }

    #chat-area { flex: 1; display: flex; flex-direction: column; }
    #chat-placeholder { flex: 1; display: flex; align-items: center; justify-content: center; color: #9ca3af; }
    #chat-window { display: none; flex: 1; flex-direction: column; overflow: hidden; }
    #messages { flex: 1; overflow-y: auto; padding: 1rem; display: flex; flex-direction: column; gap: 0.5rem; }

    .message { display: flex; }
    .message--mine { justify-content: flex-end; }
    .message--theirs { justify-content: flex-start; }
    .message__bubble { max-width: 70%; padding: 0.5rem 0.75rem; border-radius: 1rem; }
    .message--mine .message__bubble { background: #2563eb; color: #fff; border-bottom-right-radius: 0.2rem; }
    .message--theirs .message__bubble { background: #e5e7eb; color: #111; border-bottom-left-radius: 0.2rem; }
    .message__body { margin: 0; word-break: break-word; }
    .message__time { font-size: 0.7rem; opacity: 0.6; display: block; margin-top: 0.2rem; }

    #message-form { display: flex; padding: 0.75rem; border-top: 1px solid #e5e7eb; gap: 0.5rem; }
    #message-input { flex: 1; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 1rem; }
    #message-form button { padding: 0.5rem 1.25rem; background: #2563eb; color: #fff; border: none; border-radius: 0.5rem; cursor: pointer; }
    #message-form button:disabled { opacity: 0.5; cursor: not-allowed; }
    #message-form button:hover:not(:disabled) { background: #1d4ed8; }
  </style>
</head>
<body>

  <div id="sidebar">
    <h2>Messages</h2>
    <ul id="conversation-list"></ul>
  </div>

  <div id="chat-area">
    <div id="chat-placeholder">Select a conversation to start chatting</div>
    <div id="chat-window">
      <div id="messages"></div>
      <form id="message-form">
        <input id="message-input" type="text" placeholder="Type a message..." autocomplete="off" maxlength="5000" />
        <button type="submit">Send</button>
      </form>
    </div>
  </div>

  <script type="module">
    import { auth } from './auth.js';
    import { ConversationList } from './conversations.js';
    import { Chat } from './chat.js';
    import { disconnectPusher } from './pusher-client.js';

    // Guard: redirect if not authenticated
    if (!auth.isAuthenticated()) {
      window.location.href = '/login';
    }

    const user = auth.getUser();
    // activeProfileId: set this after login if the user is acting as a worker profile
    const activeProfileId = null;

    let activeChat = null;

    const list = new ConversationList(
      document.getElementById('conversation-list'),
      async (conv) => {
        if (activeChat) activeChat.destroy();

        document.getElementById('chat-placeholder').style.display = 'none';
        const chatWindow = document.getElementById('chat-window');
        chatWindow.style.display = 'flex';

        activeChat = new Chat({
          profileId: conv.profile_id,
          bookingId: conv.booking_id,
          currentUserId: user.id,
          activeProfileId,
          messagesEl: document.getElementById('messages'),
          formEl: document.getElementById('message-form'),
          inputEl: document.getElementById('message-input'),
        });

        await activeChat.init();
      }
    );

    await list.load();

    // Disconnect WebSocket on page unload (tab close / navigation)
    window.addEventListener('beforeunload', () => {
      if (activeChat) activeChat.destroy();
      disconnectPusher();
    });
  </script>
</body>
</html>
```

---

## Flow

```
Page loads
  └─ auth.isAuthenticated() → redirect to /login if false
  └─ ConversationList.load() → GET /api/conversations → render sidebar

User clicks a conversation
  └─ Chat.init()
       ├─ GET /api/messages?profile_id=X  → render history (textContent, no innerHTML)
       ├─ PATCH /api/conversations/{id}/read
       └─ getPusher().subscribe('private-chat.user.{id}')
            └─ authorizer() → POST /broadcasting/auth (fresh token each time)
            └─ 'message.sent' → _appendMessage() via textContent

User submits form
  └─ Length check (≤ 5000)
  └─ _sending flag prevents double-submit
  └─ POST /api/messages → _appendMessage()
  └─ On failure: restore input value + show error

Tab closes / navigates away
  └─ beforeunload → Chat.destroy() + disconnectPusher()
```

---

## Notes

- **No `innerHTML` for user content** — all message bodies use `textContent`. Only profile names (from your own API) use `innerHTML` after `_escape()`.
- **`sessionStorage` over `localStorage`** — tokens are not persisted to disk and are wiped when the browser tab closes.
- **Pusher `authorizer`** — reads token fresh per channel auth, not once at construction.
- **`beforeunload` handler** — closes the WebSocket cleanly when the user navigates away, preventing ghost connections.
- **Double-submit guard** — `_sending` flag and `disabled` button prevent duplicate messages on slow networks.
