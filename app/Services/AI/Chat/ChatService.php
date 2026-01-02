<?php

namespace App\Services\AI\Chat;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Company;
use App\Models\User;
use App\Services\AI\AIServiceFactory;
use App\Services\AI\Contracts\AIServiceInterface;
use Illuminate\Support\Facades\DB;

class ChatService
{
    protected AIServiceInterface $aiService;

    public function __construct(
        protected ToolRegistry $registry,
        protected ToolExecutor $executor
    ) {
        // Use factory to get configured AI provider (Ollama by default, free!)
        $this->aiService = AIServiceFactory::make();
    }

    /**
     * Start a new conversation.
     */
    public function startConversation(User $user, ?Company $company = null): ChatConversation
    {
        return ChatConversation::create([
            'user_id' => $user->id,
            'company_id' => $company?->id,
            'context_type' => $company ? 'tenant' : 'superadmin',
            'last_message_at' => now(),
        ]);
    }

    /**
     * Send a message and get response.
     */
    public function sendMessage(ChatConversation $conversation, string $userMessage): array
    {
        return DB::transaction(function () use ($conversation, $userMessage) {
            // Create user message
            $userMsg = ChatMessage::create([
                'conversation_id' => $conversation->id,
                'role' => 'user',
                'content' => $userMessage,
            ]);

            // Get conversation history for context
            $history = $this->getConversationHistory($conversation);

            // Get available tools for user
            $tools = $this->registry->getToolsForContext(
                $conversation->user,
                $conversation->company
            );

            // Get system prompt
            $systemPrompt = config(
                $conversation->isSuperadminContext()
                    ? 'ai.system_prompts.superadmin'
                    : 'ai.system_prompts.tenant'
            );

            // Send to Claude
            $response = $this->aiService->sendMessage(
                $history,
                array_values($tools),
                $systemPrompt
            );

            // Check if Claude wants to use tools
            if ($this->aiService->hasToolUses($response)) {
                return $this->handleToolUses($conversation, $response, $history, $tools, $systemPrompt);
            }

            // No tools, just text response
            return $this->createAssistantMessage($conversation, $response);
        });
    }

    /**
     * Handle tool execution requests from Claude.
     */
    protected function handleToolUses(
        ChatConversation $conversation,
        array $response,
        array $history,
        array $tools,
        string $systemPrompt
    ): array {
        // Extract tool uses
        $toolUses = $this->aiService->extractToolUses($response);
        $toolResults = [];

        // Execute each tool
        foreach ($toolUses as $toolUse) {
            $result = $this->executor->execute(
                toolName: $toolUse['name'],
                input: $toolUse['input'],
                user: $conversation->user,
                company: $conversation->company,
                conversationId: $conversation->id
            );

            // Store result with tool use ID
            $toolResults[$toolUse['id']] = $result;
        }

        // Format tool results for Claude
        $formattedResults = $this->aiService->formatToolResults($toolResults);

        // Send tool results back to Claude for final response
        $history[] = [
            'role' => 'assistant',
            'content' => $response['content'], // Include original response with tool_use blocks
        ];
        $history[] = [
            'role' => 'user',
            'content' => $formattedResults,
        ];

        $finalResponse = $this->aiService->sendMessage($history, array_values($tools), $systemPrompt);

        // Create assistant message with tool info
        return $this->createAssistantMessage($conversation, $finalResponse, [
            'tool_calls' => $toolUses,
            'tool_results' => $toolResults,
        ]);
    }

    /**
     * Create assistant message from Claude response.
     */
    protected function createAssistantMessage(
        ChatConversation $conversation,
        array $response,
        array $additionalData = []
    ): array {
        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $this->aiService->extractTextContent($response),
            'tool_calls' => $additionalData['tool_calls'] ?? null,
            'tool_results' => $additionalData['tool_results'] ?? null,
            'input_tokens' => $response['usage']['input_tokens'] ?? null,
            'output_tokens' => $response['usage']['output_tokens'] ?? null,
        ]);

        return [
            'conversation_id' => $conversation->id,
            'message_id' => $message->id,
            'response' => $message->content,
            'tool_calls' => $message->tool_calls,
            'cost' => $message->cost,
            'timestamp' => $message->created_at,
        ];
    }

    /**
     * Get conversation history formatted for Claude.
     */
    protected function getConversationHistory(ChatConversation $conversation, int $limit = null): array
    {
        $limit = $limit ?? config('ai.chat.context_window_messages', 20);

        $messages = $conversation->messages()
            ->latest()
            ->limit($limit)
            ->get()
            ->reverse();

        return $messages->map(function ($msg) {
            return [
                'role' => $msg->role,
                'content' => $msg->content,
            ];
        })->toArray();
    }

    /**
     * Get conversation with messages.
     */
    public function getConversation(string $conversationId, User $user): ?ChatConversation
    {
        return ChatConversation::with('messages')
            ->where('id', $conversationId)
            ->where('user_id', $user->id)
            ->first();
    }

    /**
     * Get user's conversations.
     */
    public function getUserConversations(User $user, ?Company $company = null)
    {
        $query = ChatConversation::where('user_id', $user->id)
            ->active()
            ->orderBy('last_message_at', 'desc');

        if ($company) {
            $query->where('company_id', $company->id);
        }

        return $query->paginate(20);
    }
}
