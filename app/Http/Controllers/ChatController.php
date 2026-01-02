<?php

namespace App\Http\Controllers;

use App\Models\ChatConversation;
use App\Models\Company;
use App\Services\AI\Chat\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function __construct(
        protected ChatService $chatService
    ) {}

    /**
     * Get user's conversations.
     */
    public function index(Request $request)
    {
        $company = Company::current();

        $conversations = $this->chatService->getUserConversations(
            auth()->user(),
            $company
        );

        return response()->json($conversations);
    }

    /**
     * Get a specific conversation with messages.
     */
    public function show(string $conversationId)
    {
        $conversation = $this->chatService->getConversation(
            $conversationId,
            auth()->user()
        );

        if (!$conversation) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        return response()->json([
            'conversation' => $conversation,
            'messages' => $conversation->messages->map(fn($msg) => [
                'id' => $msg->id,
                'role' => $msg->role,
                'content' => $msg->content,
                'tool_calls' => $msg->tool_calls,
                'created_at' => $msg->created_at,
            ]),
        ]);
    }

    /**
     * Send a message and get response.
     */
    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'conversation_id' => 'nullable|uuid|exists:chat_conversations,id',
            'message' => 'required|string|max:5000',
        ]);

        try {
            $company = Company::current();

            // Get or create conversation
            if (!empty($validated['conversation_id'])) {
                $conversation = $this->chatService->getConversation(
                    $validated['conversation_id'],
                    auth()->user()
                );

                if (!$conversation) {
                    return response()->json(['error' => 'Conversation not found'], 404);
                }
            } else {
                // Start new conversation
                $conversation = $this->chatService->startConversation(
                    auth()->user(),
                    $company
                );
            }

            // Send message
            $response = $this->chatService->sendMessage(
                $conversation,
                $validated['message']
            );

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Chat error', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Une erreur est survenue. Veuillez réessayer.',
                'details' => app()->isProduction() ? null : $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete/archive a conversation.
     */
    public function destroy(string $conversationId)
    {
        $conversation = ChatConversation::where('id', $conversationId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$conversation) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        $conversation->archive();

        return response()->json(['message' => 'Conversation archived successfully']);
    }

    /**
     * Confirm tool execution.
     */
    public function confirmTool(string $executionId)
    {
        $execution = \App\Models\ChatToolExecution::findOrFail($executionId);

        // Verify ownership through message -> conversation -> user
        if ($execution->message->conversation->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Confirm execution
        $execution->confirm();

        // Re-execute the tool (implementation depends on your needs)
        // For now, just return success
        return response()->json([
            'message' => 'Tool execution confirmed',
            'execution' => $execution,
        ]);
    }
}
