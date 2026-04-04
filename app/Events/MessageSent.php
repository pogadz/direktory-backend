<?php

namespace App\Events;

use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Message $message,
        public readonly Conversation $conversation,
    ) {}

    public function broadcastOn(): array
    {
        return [
            // Broadcast to both participants messages
            new PrivateChannel('chat.user.' . $this->conversation->user_id),
            new PrivateChannel('chat.profile.' . $this->conversation->profile_id),

            // Broadcast For conversation
            new PrivateChannel('conversation.user.' . $this->conversation->user_id),
            new PrivateChannel('conversation.profile.' . $this->conversation->profile_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'conversation_id' => $this->conversation->id,
            'body' => $this->message->body,
            'created_at' => $this->message->created_at->toISOString(),
            'sender_user_id' => $this->message->sender_user_id,
            'sender_profile_id' => $this->message->sender_profile_id,
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }
}
