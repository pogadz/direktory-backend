# Chat System

Real-time chat between users and workers (profiles) using WebSockets via Soketi.

---

## Overview

- Users can message any worker profile (optionally tied to a booking)
- Messages are delivered in real-time via private WebSocket channels
- Conversations track read/unread state per participant
- Authentication uses Sanctum tokens for both REST and channel authorization

---

## Setup

### 1. Install dependencies

```bash
composer require pusher/pusher-php-server
npm install pusher-js
```

### 2. Run migrations

```bash
php artisan migrate
```

### 3. Configure environment

Copy the Soketi variables from `.env.example` into your `.env`:

```env
BROADCAST_DRIVER=soketi

PUSHER_APP_ID=direktory-app
PUSHER_APP_KEY=direktory-key
PUSHER_APP_SECRET=direktory-secret
PUSHER_APP_CLUSTER=mt1
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http
```

### 4. Start Soketi

Soketi runs as a Docker service alongside the app. Start it with:

```bash
docker compose up -d soketi
```

Or bring up the full stack:

```bash
docker compose up -d
```

Soketi will be available at `ws://127.0.0.1:6001`. The metrics/health endpoint is at `http://127.0.0.1:9601`.

---

## API Endpoints

All endpoints require `Authorization: Bearer {token}` (Sanctum).

### List Conversations

```
GET /api/conversations
```

Returns all conversations for the authenticated user or active profile, ordered by most recent message. Includes unread count per conversation.

**Response**
```json
{
  "data": [
    {
      "id": 1,
      "booking_id": 5,
      "user_id": 2,
      "profile_id": 3,
      "unread_count": 2,
      "latest_message": {
        "id": 14,
        "body": "See you tomorrow!",
        "created_at": "2026-03-24T10:00:00.000Z"
      },
      "profile": { "id": 3, "name": "Juan dela Cruz" },
      "user": { "id": 2, "firstname": "Maria" }
    }
  ]
}
```

---

### Get or Open a Conversation

```
POST /api/conversations/open
```

Finds or creates a conversation between the current user and the given profile. Returns the conversation and all messages. Automatically marks the conversation as read for the caller.

**Request Body**

| Parameter | Required | Description |
|-----------|----------|-------------|
| `profile_id` | Yes | The worker profile to converse with |
| `booking_id` | No | Link conversation to a specific booking |
| `user_id` | Conditional | Required when acting as a profile (to identify the other participant) |

**Response**
```json
{
  "data": {
    "conversation": {
      "id": 1,
      "user_id": 2,
      "profile_id": 3,
      "booking_id": 5
    },
    "messages": [
      {
        "id": 1,
        "body": "Hi, I need help with plumbing.",
        "sender_user_id": 2,
        "sender_profile_id": null,
        "created_at": "2026-03-24T09:00:00.000Z"
      }
    ]
  }
}
```

---

### Send a Message

```
POST /api/messages
```

Sends a message in an existing conversation. Broadcasts to all other participants via Soketi in real-time.

**Request Body**
```json
{
  "conversation_id": 1,
  "body": "I can be there at 9am."
}
```

**Response** `201 Created`
```json
{
  "data": {
    "id": 2,
    "conversation_id": 1,
    "body": "I can be there at 9am.",
    "sender_user_id": 3,
    "sender_profile_id": 3,
    "created_at": "2026-03-24T09:05:00.000Z"
  }
}
```

---

### Mark Conversation as Read

```
PATCH /api/conversations/{conversation}/read
```

Updates the `user_last_read_at` or `profile_last_read_at` timestamp for the caller.

**Response**
```json
{ "status": "ok" }
```

---

## Real-Time (WebSocket)

### Channels

| Channel | Subscriber |
|---------|-----------|
| `private-chat.user.{userId}` | Plain user (no active profile) |
| `private-chat.profile.{profileId}` | Worker acting as a profile |

### Subscribing (Frontend)

```js
import Pusher from 'pusher-js';

const pusher = new Pusher(process.env.PUSHER_APP_KEY, {
  wsHost: '127.0.0.1',
  wsPort: 6001,
  forceTLS: false,
  enabledTransports: ['ws'],
  cluster: 'mt1',
  authEndpoint: '/broadcasting/auth',
  auth: {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  },
});

// Subscribe as a plain user
const channel = pusher.subscribe(`private-chat.user.${userId}`);

// Subscribe as a profile
const channel = pusher.subscribe(`private-chat.profile.${profileId}`);

channel.bind('message.sent', (data) => {
  console.log('New message:', data);
});
```

### Broadcast Payload

```json
{
  "id": 15,
  "conversation_id": 1,
  "body": "I can be there at 9am.",
  "sender_user_id": 3,
  "sender_profile_id": 3,
  "created_at": "2026-03-24T09:05:00.000Z"
}
```

---

## Database Schema

### `conversations`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `booking_id` | bigint (nullable FK) | Optional linked booking |
| `user_id` | bigint (FK) | The plain user participant |
| `profile_id` | bigint (FK) | The worker profile participant |
| `user_last_read_at` | timestamp (nullable) | When the user last read |
| `profile_last_read_at` | timestamp (nullable) | When the profile last read |

Unique constraint on `(booking_id, user_id, profile_id)`.

### `messages`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `conversation_id` | bigint (FK) | Owning conversation |
| `sender_user_id` | bigint (FK → users) | Always set |
| `sender_profile_id` | bigint (nullable FK → profiles) | Set when sent as a profile |
| `body` | text | Message content |

---

## Authorization Rules

| Action | Rule |
|--------|------|
| Subscribe to `chat.user.{id}` | Token user ID must match `{id}` |
| Subscribe to `chat.profile.{id}` | Token user must own a profile with `{id}` |
| Send a message | Caller must be a participant of the conversation |
| Read messages | Caller must be a participant of the conversation |

---

## File Reference

| File | Purpose |
|------|---------|
| `app/Models/Conversation.php` | Conversation model |
| `app/Models/Message.php` | Message model |
| `app/Repositories/Contracts/MessageRepositoryInterface.php` | Repository interface |
| `app/Repositories/Queries/MessageRepository.php` | Repository implementation |
| `app/Events/MessageSent.php` | Broadcast event |
| `app/Http/Controllers/Api/MessageController.php` | API controller |
| `routes/channels.php` | WebSocket channel authorization |
| `config/broadcasting.php` | Broadcasting driver config |
