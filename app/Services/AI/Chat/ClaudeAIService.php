<?php

namespace App\Services\AI\Chat;

use App\Services\AI\Contracts\AIServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeAIService implements AIServiceInterface
{
    protected ?string $apiKey;
    protected ?string $model;
    protected ?string $baseUrl;
    protected ?string $apiVersion;
    protected ?int $maxTokens;
    protected ?float $temperature;

    public function __construct()
    {
        $this->apiKey = config('ai.claude.api_key');
        $this->model = config('ai.claude.model', 'claude-3-5-sonnet-20241022');
        $this->baseUrl = config('ai.claude.base_url', 'https://api.anthropic.com/v1');
        $this->apiVersion = config('ai.claude.api_version', '2023-06-01');
        $this->maxTokens = config('ai.claude.max_tokens', 4096);
        $this->temperature = config('ai.claude.temperature', 0.7);
    }

    /**
     * Get provider name
     */
    public function getProviderName(): string
    {
        return 'claude';
    }

    /**
     * Check if Claude API is available
     */
    public function isAvailable(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Ensure API is configured before use
     */
    protected function ensureConfigured(): void
    {
        if (empty($this->apiKey)) {
            throw new \Exception('Claude API key not configured. Please set CLAUDE_API_KEY in .env');
        }
    }

    /**
     * Send a message to Claude with optional tools.
     *
     * @param array $messages Array of messages in Claude format
     * @param array $tools Array of tool definitions
     * @param string|null $system Optional system prompt
     * @return array Claude's response
     */
    public function sendMessage(array $messages, array $tools = [], ?string $system = null): array
    {
        $this->ensureConfigured();

        $payload = [
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
            'messages' => $messages,
        ];

        if ($system) {
            $payload['system'] = $system;
        }

        if (!empty($tools)) {
            $payload['tools'] = $this->formatToolDefinitions($tools);
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => $this->apiVersion,
                'content-type' => 'application/json',
            ])
            ->timeout(60)
            ->post($this->baseUrl . '/messages', $payload);

            if ($response->failed()) {
                Log::error('Claude API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Claude API request failed: ' . $response->body());
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::error('Claude API exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Format internal tool definitions to Claude's expected schema.
     *
     * @param array $tools Array of AbstractTool instances
     * @return array
     */
    public function formatToolDefinitions(array $tools): array
    {
        return array_map(function ($tool) {
            return [
                'name' => $tool->getName(),
                'description' => $tool->getDescription(),
                'input_schema' => $tool->getInputSchema(),
            ];
        }, $tools);
    }

    /**
     * Extract tool use blocks from Claude's response.
     *
     * @param array $response Claude's response
     * @return array Array of tool use requests
     */
    public function extractToolUses(array $response): array
    {
        $toolUses = [];

        if (isset($response['content']) && is_array($response['content'])) {
            foreach ($response['content'] as $block) {
                if (isset($block['type']) && $block['type'] === 'tool_use') {
                    $toolUses[] = [
                        'id' => $block['id'],
                        'name' => $block['name'],
                        'input' => $block['input'],
                    ];
                }
            }
        }

        return $toolUses;
    }

    /**
     * Extract text content from Claude's response.
     *
     * @param array $response Claude's response
     * @return string
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
     * Calculate API cost based on token usage.
     *
     * @param int $inputTokens
     * @param int $outputTokens
     * @return float Cost in dollars
     */
    public function calculateCost(int $inputTokens, int $outputTokens): float
    {
        $inputCost = ($inputTokens / 1_000_000) * config('ai.costs.input_per_million');
        $outputCost = ($outputTokens / 1_000_000) * config('ai.costs.output_per_million');

        return round($inputCost + $outputCost, 6);
    }

    /**
     * Format tool results for sending back to Claude.
     *
     * @param array $toolResults Array of [id => result]
     * @return array
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
     * Check if response contains tool uses.
     *
     * @param array $response
     * @return bool
     */
    public function hasToolUses(array $response): bool
    {
        return !empty($this->extractToolUses($response));
    }

    /**
     * Get stop reason from response.
     *
     * @param array $response
     * @return string|null
     */
    public function getStopReason(array $response): ?string
    {
        return $response['stop_reason'] ?? null;
    }
}
