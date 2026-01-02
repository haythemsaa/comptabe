<?php

namespace App\Services\AI\Contracts;

interface AIServiceInterface
{
    /**
     * Send a message to the AI service
     *
     * @param array $messages Format: [['role' => 'user', 'content' => '...']]
     * @param array $tools Available tools for the AI to use
     * @return array Response: ['role', 'content', 'tool_calls', 'usage']
     */
    public function sendMessage(array $messages, array $tools = []): array;

    /**
     * Format tool definitions for this AI provider
     *
     * @param array $tools Array of AbstractTool instances
     * @return array Formatted tools for this provider
     */
    public function formatToolDefinitions(array $tools): array;

    /**
     * Calculate cost for this request
     *
     * @param int $inputTokens
     * @param int $outputTokens
     * @return float Cost in dollars (or 0 for free providers)
     */
    public function calculateCost(int $inputTokens, int $outputTokens): float;

    /**
     * Get the provider name
     *
     * @return string Provider identifier (claude, ollama, gemini, etc.)
     */
    public function getProviderName(): string;

    /**
     * Check if the provider is available/configured
     *
     * @return bool
     */
    public function isAvailable(): bool;
}
