<?php

namespace App\Repositories\Queries;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Repositories\Contracts\MessageRepositoryInterface;

class MessageRepository implements MessageRepositoryInterface
{
    /**
     * Get conversations for a user
     *
     * @param User $user
     * @param integer|null $profileId
     * @return \Illuminate\Support\Collection
     */
    public function getConversationsForUser(User $user, ?int $profileId = null): \Illuminate\Support\Collection
    {
        $query = Conversation::with(['profile', 'user', 'latestMessage', 'booking'])
            ->withCount(['messages as unread_count' => function ($q) use ($profileId) {
                if ($profileId) {
                    $q->whereColumn('created_at', '>', 'conversations.profile_last_read_at');
                } else {
                    $q->whereColumn('created_at', '>', 'conversations.user_last_read_at');
                }
            }]);

        if ($profileId) {
            $query->where('profile_id', $profileId);
        } else {
            $query->where('user_id', $user->id);
        }

        return $query->orderByDesc(
            Message::select('created_at')
                ->whereColumn('conversation_id', 'conversations.id')
                ->latest()
                ->limit(1)
        )->get();
    }

    /**
     * Find conversion
     *
     * @param integer $userId
     * @param integer $profileId
     * @param integer|null $bookingId
     * @return Conversation|null
     */
    public function findConversation(int $userId, int $profileId, ?int $bookingId = null): ?Conversation
    {
        $query = Conversation::where('user_id', $userId)->where('profile_id', $profileId);

        if ($bookingId) {
            $query->where('booking_id', $bookingId);
        }

        return $query->first();
    }

    /**
     * Find or create conversation
     *
     * @param integer $userId
     * @param integer $profileId
     * @param integer|null $bookingId
     * @return Conversation
     */
    public function findOrCreateConversation(int $userId, int $profileId, ?int $bookingId = null): Conversation
    {
        $attributes = [
            'user_id' => $userId,
            'profile_id' => $profileId,
        ];

        if ($bookingId) {
            $attributes['booking_id'] = $bookingId;
        }

        return Conversation::firstOrCreate($attributes);
    }

    /**
     * Get messages for a conversation
     *
     * @param Conversation $conversation
     * @return \Illuminate\Support\Collection
     */
    public function getMessages(Conversation $conversation): \Illuminate\Support\Collection
    {
        return $conversation->messages()->with(['senderUser', 'senderProfile'])->get();
    }

    public function createMessage(Conversation $conversation, int $senderUserId, ?int $senderProfileId, string $body): Message
    {
        return $conversation->messages()->create([
            'sender_user_id' => $senderUserId,
            'sender_profile_id' => $senderProfileId,
            'body' => $body,
        ]);
    }

    /**
     * Mark conversation as read
     *
     * @param Conversation $conversation
     * @param bool $asProfile
     */
    public function markRead(Conversation $conversation, bool $asProfile): void
    {
        $field = $asProfile ? 'profile_last_read_at' : 'user_last_read_at';
        $conversation->update([$field => now()]);
    }
}
