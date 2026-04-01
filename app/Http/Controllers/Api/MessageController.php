<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Services\ProfileService;
use Illuminate\Http\Request;

/**
 * @group Message
 */
class MessageController extends Controller
{
    public function __construct(
        private MessageRepositoryInterface $messageRepository,
        private ProfileService $profileService,
    ) {}

    /**
     * List conversations for the authenticated user/profile
     */
    public function conversations(Request $request)
    {
        $user = $request->user();
        $profileId = $this->profileService->getActiveProfileId($user);

        $conversations = $this->messageRepository->getConversationsForUser($user, $profileId);

        return response()->json(['data' => $conversations]);
    }

    /**
     * Get or create a conversation, then return messages
     */
    public function index(Request $request)
    {
        $request->validate([
            'profile_id' => 'required|integer|exists:profiles,id',
            'booking_id' => 'nullable|integer|exists:bookings,id',
            'user_id'    => 'nullable|integer|exists:users,id',
        ]);

        $user = $request->user();
        $activeProfileId = $this->profileService->getActiveProfileId($user);

        // Determine who is user and who is profile in this conversation
        if ($activeProfileId) {
            // Acting as a profile — must match the requested profile_id
            abort_if((int) $activeProfileId !== (int) $request->profile_id, 403, 'You can only view your own profile conversations.');
            $userId = $request->input('user_id');
            abort_if(!$userId, 422, 'user_id is required when acting as a profile.');

            $conversation = $this->messageRepository->findOrCreateConversation(
                (int) $userId,
                (int) $activeProfileId,
                $request->booking_id ? (int) $request->booking_id : null,
            );
        } else {
            // Acting as a plain user
            $conversation = $this->messageRepository->findOrCreateConversation(
                $user->id,
                (int) $request->profile_id,
                $request->booking_id ? (int) $request->booking_id : null,
            );
        }

        // Mark as read
        $this->messageRepository->markRead($conversation, (bool) $activeProfileId);

        $messages = $this->messageRepository->getMessages($conversation);

        return response()->json([
            'data' => [
                'conversation' => $conversation,
                'messages' => $messages,
            ]
        ]);
    }

    /**
     * Send a message
     */
    public function store(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|integer|exists:conversations,id',
            'body' => 'required|string|max:5000',
        ]);

        $user = $request->user();
        $activeProfileId = $this->profileService->getActiveProfileId($user);

        $conversation = \App\Models\Conversation::findOrFail($request->conversation_id);

        // Authorization: user must be a participant
        if ($activeProfileId) {
            abort_if((int) $activeProfileId !== (int) $conversation->profile_id, 403, 'Not a participant of this conversation.');
        } else {
            abort_if((int) $user->id !== (int) $conversation->user_id, 403, 'Not a participant of this conversation.');
        }

        $message = $this->messageRepository->createMessage(
            $conversation,
            $user->id,
            $activeProfileId ? (int) $activeProfileId : null,
            $request->body,
        );

        broadcast(new MessageSent($message, $conversation))->toOthers();

        return response()->json(['data' => $message], 201);
    }

    /**
     * Mark conversation as read
     */
    public function markRead(Request $request, int $conversationId)
    {
        $user = $request->user();
        $activeProfileId = $this->profileService->getActiveProfileId($user);

        $conversation = \App\Models\Conversation::findOrFail($conversationId);

        if ($activeProfileId) {
            abort_if((int) $activeProfileId !== (int) $conversation->profile_id, 403);
        } else {
            abort_if((int) $user->id !== (int) $conversation->user_id, 403);
        }

        $this->messageRepository->markRead($conversation, (bool) $activeProfileId);

        return response()->json(['status' => 'ok']);
    }
}
