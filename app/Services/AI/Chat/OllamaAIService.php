<?php

namespace App\Services\AI\Chat;

use App\Services\AI\Contracts\AIServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Ollama AI Service - FREE local LLM provider
 *
 * Supports models like:
 * - llama3.1 (8B, 70B)
 * - mistral (7B)
 * - phi3 (3.8B - very fast)
 * - qwen2.5 (excellent for code)
 *
 * Installation: https://ollama.com/download
 * Run: ollama run llama3.1
 */
class OllamaAIService implements AIServiceInterface
{
    protected string $baseUrl;
    protected string $model;
    protected float $temperature;
    protected int $maxTokens;

    public function __construct()
    {
        $this->baseUrl = config('ai.ollama.base_url', 'http://localhost:11434');
        $this->model = config('ai.ollama.model', 'llama3.1');
        $this->temperature = config('ai.ollama.temperature', 0.7);
        $this->maxTokens = config('ai.ollama.max_tokens', 4096);
    }

    /**
     * Check if Ollama is available
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(2)->get($this->baseUrl . '/api/tags');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get provider name
     */
    public function getProviderName(): string
    {
        return 'ollama';
    }

    /**
     * Send message to Ollama with tool support
     */
    public function sendMessage(array $messages, array $tools = []): array
    {
        if (!$this->isAvailable()) {
            throw new \Exception(
                'Ollama not available. Please install and start Ollama: ' .
                'https://ollama.com/download then run: ollama run ' . $this->model
            );
        }

        // Convert messages to Ollama format
        $prompt = $this->formatMessagesAsPrompt($messages, $tools);

        try {
            $response = Http::timeout(120)
                ->post($this->baseUrl . '/api/generate', [
                    'model' => $this->model,
                    'prompt' => $prompt,
                    'stream' => false,
                    'options' => [
                        'temperature' => $this->temperature,
                        'num_predict' => $this->maxTokens,
                    ],
                ]);

            if ($response->failed()) {
                Log::error('Ollama API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Ollama request failed: ' . $response->body());
            }

            $data = $response->json();

            // Parse response for tool calls
            $responseText = $data['response'] ?? '';
            $toolCalls = $this->extractToolCallsFromText($responseText, $tools);

            // Return in standard format
            return [
                'role' => 'assistant',
                'content' => [
                    ['type' => 'text', 'text' => $responseText]
                ],
                'tool_calls' => $toolCalls,
                'usage' => [
                    'input_tokens' => $data['prompt_eval_count'] ?? 0,
                    'output_tokens' => $data['eval_count'] ?? 0,
                ],
                'stop_reason' => $data['done'] ? 'end_turn' : 'max_tokens',
            ];

        } catch (\Exception $e) {
            Log::error('Ollama exception', [
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Format messages and tools as a prompt for Ollama
     */
    protected function formatMessagesAsPrompt(array $messages, array $tools): string
    {
        $prompt = '';

        // Add system message with tools info
        if (!empty($tools)) {
            $prompt .= "You are an AI assistant for ComptaBE, a Belgian accounting platform. ";
            $prompt .= "You help users with invoices, VAT declarations, accounting, and business management. ";
            $prompt .= "Answer in French professionally and concisely.\n\n";
            $prompt .= "You have access to the following tools:\n\n";

            foreach ($tools as $tool) {
                $prompt .= "- **{$tool->getName()}**: {$tool->getDescription()}\n";
                $prompt .= "  Parameters: " . json_encode($tool->getInputSchema(), JSON_PRETTY_PRINT) . "\n\n";
            }

            $prompt .= "To use a tool, respond with JSON in this format:\n";
            $prompt .= "```json\n{\n  \"tool\": \"tool_name\",\n  \"parameters\": {...}\n}\n```\n\n";
            $prompt .= "If you don't need a tool, just answer the question directly.\n\n";
        } else {
            $prompt .= "You are an AI assistant for ComptaBE. Answer in French.\n\n";
        }

        // Add conversation history
        foreach ($messages as $message) {
            $role = $message['role'];
            $content = is_array($message['content'])
                ? $this->extractTextFromContent($message['content'])
                : $message['content'];

            if ($role === 'user') {
                $prompt .= "User: {$content}\n\n";
            } elseif ($role === 'assistant') {
                $prompt .= "Assistant: {$content}\n\n";
            }
        }

        $prompt .= "Assistant: ";

        return $prompt;
    }

    /**
     * Extract text from content blocks
     */
    protected function extractTextFromContent(array $content): string
    {
        $texts = [];
        foreach ($content as $block) {
            if (isset($block['type']) && $block['type'] === 'text') {
                $texts[] = $block['text'];
            }
        }
        return implode("\n", $texts);
    }

    /**
     * Extract tool calls from response text
     * Looks for JSON blocks with tool/parameters
     */
    protected function extractToolCallsFromText(string $text, array $tools): array
    {
        $toolCalls = [];

        // Try to find JSON blocks
        if (preg_match('/```json\s*(\{[^`]+\})\s*```/s', $text, $matches)) {
            try {
                $json = json_decode($matches[1], true);

                if (isset($json['tool']) && isset($json['parameters'])) {
                    // Verify tool exists
                    $toolExists = false;
                    foreach ($tools as $tool) {
                        if ($tool->getName() === $json['tool']) {
                            $toolExists = true;
                            break;
                        }
                    }

                    if ($toolExists) {
                        $toolCalls[] = [
                            'id' => 'tool_' . uniqid(),
                            'name' => $json['tool'],
                            'input' => $json['parameters'],
                        ];
                    }
                }
            } catch (\Exception $e) {
                // JSON parsing failed, no tool call
                Log::debug('Failed to parse tool call JSON', ['error' => $e->getMessage()]);
            }
        }

        return $toolCalls;
    }

    /**
     * Format tool definitions for Ollama (embedded in prompt)
     */
    public function formatToolDefinitions(array $tools): array
    {
        // Ollama doesn't have native tool support like Claude
        // Tools are described in the prompt instead
        return array_map(function ($tool) {
            return [
                'name' => $tool->getName(),
                'description' => $tool->getDescription(),
                'input_schema' => $tool->getInputSchema(),
            ];
        }, $tools);
    }

    /**
     * Calculate cost - FREE!
     */
    public function calculateCost(int $inputTokens, int $outputTokens): float
    {
        return 0.0; // Ollama is completely free
    }

    /**
     * Extract tool uses (compatible with ClaudeAIService interface)
     */
    public function extractToolUses(array $response): array
    {
        return $response['tool_calls'] ?? [];
    }

    /**
     * Extract text content
     */
    public function extractTextContent(array $response): string
    {
        if (isset($response['content']) && is_array($response['content'])) {
            $texts = [];
            foreach ($response['content'] as $block) {
                if (isset($block['type']) && $block['type'] === 'text') {
                    $texts[] = $block['text'];
                }
            }
            return implode("\n\n", $texts);
        }

        return '';
    }

    /**
     * Check if response has tool uses
     */
    public function hasToolUses(array $response): bool
    {
        return !empty($response['tool_calls'] ?? []);
    }

    /**
     * Get stop reason
     */
    public function getStopReason(array $response): ?string
    {
        return $response['stop_reason'] ?? null;
    }

    /**
     * Format tool results for next message
     */
    public function formatToolResults(array $toolResults): array
    {
        $results = [];

        foreach ($toolResults as $toolUseId => $result) {
            $results[] = [
                'type' => 'tool_result',
                'tool_use_id' => $toolUseId,
                'content' => is_string($result) ? $result : json_encode($result),
            ];
        }

        return $results;
    }

    /**
     * List available models
     */
    public function listModels(): array
    {
        try {
            $response = Http::get($this->baseUrl . '/api/tags');

            if ($response->successful()) {
                $data = $response->json();
                return $data['models'] ?? [];
            }
        } catch (\Exception $e) {
            Log::error('Failed to list Ollama models', ['error' => $e->getMessage()]);
        }

        return [];
    }

    /**
     * Pull a model from Ollama registry
     */
    public function pullModel(string $model): bool
    {
        try {
            $response = Http::timeout(300)
                ->post($this->baseUrl . '/api/pull', [
                    'name' => $model,
                    'stream' => false,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Failed to pull Ollama model', [
                'model' => $model,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
