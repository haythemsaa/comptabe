<?php

namespace App\Services\AI\Chat;

use App\Models\Company;
use App\Models\User;
use App\Services\AI\Chat\Tools\AbstractTool;

class ToolRegistry
{
    protected array $tools = [];

    /**
     * Tools are registered in AppServiceProvider::boot()
     * via the registerChatTools() method.
     */
    public function __construct()
    {
        // Tools will be registered by AppServiceProvider
    }

    /**
     * Register a tool.
     */
    public function register(AbstractTool $tool): void
    {
        $this->tools[$tool->getName()] = $tool;
    }

    /**
     * Get a specific tool by name.
     */
    public function getTool(string $name): ?AbstractTool
    {
        return $this->tools[$name] ?? null;
    }

    /**
     * Get all available tools for a given user and context.
     */
    public function getToolsForContext(User $user, ?Company $company): array
    {
        $allowedToolNames = $this->getAllowedToolNames($user);

        return array_filter($this->tools, function ($tool) use ($allowedToolNames) {
            return in_array($tool->getName(), $allowedToolNames);
        });
    }

    /**
     * Get allowed tool names based on user role.
     */
    protected function getAllowedToolNames(User $user): array
    {
        $tenantTools = config('ai.tools.tenant', []);
        $firmTools = config('ai.tools.firm', []);
        $superadminTools = config('ai.tools.superadmin', []);

        $allowedTools = $tenantTools;

        // Add firm tools if user is a firm member
        if ($user->isCabinetMember()) {
            $allowedTools = array_merge($allowedTools, $firmTools);
        }

        // Add superadmin tools if user is superadmin
        if ($user->isSuperadmin()) {
            $allowedTools = array_merge($allowedTools, $superadminTools);
        }

        return $allowedTools;
    }

    /**
     * Get tool definitions for Claude API.
     */
    public function getToolDefinitions(array $tools): array
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
     * Get all registered tools.
     */
    public function getAllTools(): array
    {
        return $this->tools;
    }
}
