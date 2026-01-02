<?php

namespace App\Services\AI\Chat\Tools;

use App\Models\Company;
use App\Models\User;

abstract class AbstractTool
{
    /**
     * Get the tool's unique name.
     */
    abstract public function getName(): string;

    /**
     * Get the tool's description for Claude.
     */
    abstract public function getDescription(): string;

    /**
     * Get the JSON schema for tool input parameters.
     */
    abstract public function getInputSchema(): array;

    /**
     * Execute the tool with given input and context.
     *
     * @param array $input Tool parameters
     * @param ToolContext $context Execution context
     * @return array Result of execution
     */
    abstract public function execute(array $input, ToolContext $context): array;

    /**
     * Whether this tool requires user confirmation before execution.
     */
    public function requiresConfirmation(): bool
    {
        return false;
    }

    /**
     * Check if user has permission to execute this tool.
     */
    protected function checkPermission(User $user, string $ability, string $model): void
    {
        if (!$user->can($ability, $model)) {
            throw new \Exception("Permission denied: Cannot {$ability} {$model}");
        }
    }

    /**
     * Validate user access to company.
     */
    protected function validateTenantAccess(User $user, ?Company $company): void
    {
        if ($company && !$user->hasAccessToCompany($company->id)) {
            throw new \Exception('No access to this company');
        }
    }
}

/**
 * Context object passed to tool execution.
 */
class ToolContext
{
    public function __construct(
        public User $user,
        public ?Company $company,
        public string $conversationId,
        public bool $confirmed = false
    ) {}
}
