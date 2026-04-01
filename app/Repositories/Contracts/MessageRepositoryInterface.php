<?php

namespace App\Repositories\Contracts;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

interface MessageRepositoryInterface
{
    public function getConversationsForUser(User $user, ?int $profileId = null): \Illuminate\Support\Collection;
    
    public function findConversation(int $userId, int $profileId, ?int $bookingId = null): ?Conversation;
    
    public function findOrCreateConversation(int $userId, int $profileId, ?int $bookingId = null): Conversation;
    
    public function getMessages(Conversation $conversation): \Illuminate\Support\Collection;
    
    public function createMessage(Conversation $conversation, int $senderUserId, ?int $senderProfileId, string $body): Message;
    
    public function markRead(Conversation $conversation, bool $asProfile): void;
}
