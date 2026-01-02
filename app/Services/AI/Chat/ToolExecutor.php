<?php

namespace App\Services\AI\Chat;

use App\Models\AuditLog;
use App\Models\ChatToolExecution;
use App\Models\Company;
use App\Models\User;
use App\Services\AI\Chat\Tools\AbstractTool;
use App\Services\AI\Chat\Tools\ToolContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ToolExecutor
{
    public function __construct(
        protected ToolRegistry $registry
    ) {}

    /**
     * Execute a tool with security checks.
     *
     * @param string $toolName
     * @param array $input
     * @param User $user
     * @param Company|null $company
     * @param string $conversationId
     * @param string|null $messageId For logging
     * @return array
     */
    public function execute(
        string $toolName,
        array $input,
        User $user,
        ?Company $company,
        string $conversationId,
        ?string $messageId = null
    ): array {
        // Get tool instance
        $tool = $this->registry->getTool($toolName);
        if (!$tool) {
            throw new \Exception("Tool '{$toolName}' not found");
        }

        // Check if user is allowed to use this tool
        $this->validateToolAccess($tool, $user, $company);

        // Validate input against schema
        $this->validateInput($input, $tool->getInputSchema());

        // Create tool execution record
        $execution = null;
        if ($messageId) {
            $execution = ChatToolExecution::create([
                'message_id' => $messageId,
                'tool_name' => $toolName,
                'tool_input' => $input,
                'status' => 'pending',
                'requires_confirmation' => $tool->requiresConfirmation(),
            ]);
        }

        // Check if confirmation required and not yet confirmed
        if ($tool->requiresConfirmation() && (!$execution || !$execution->confirmed)) {
            return [
                'requires_confirmation' => true,
                'execution_id' => $execution?->id,
                'message' => 'This action requires confirmation. Please confirm to proceed.',
            ];
        }

        try {
            // Create execution context
            $context = new ToolContext(
                user: $user,
                company: $company,
                conversationId: $conversationId,
                confirmed: $execution?->confirmed ?? false
            );

            // Execute tool within transaction
            $result = DB::transaction(function () use ($tool, $input, $context) {
                return $tool->execute($input, $context);
            });

            // Mark execution as successful
            if ($execution) {
                $execution->markAsSuccess($result);
            }

            // Audit log
            $this->logExecution($user, $company, $toolName, $input, $result, 'success');

            return [
                'success' => true,
                ...$result,
            ];

        } catch (\Exception $e) {
            // Mark execution as failed
            if ($execution) {
                $execution->markAsError($e->getMessage());
            }

            // Log error
            Log::error('Tool execution failed', [
                'tool' => $toolName,
                'input' => $input,
                'error' => $e->getMessage(),
                'user' => $user->id,
            ]);

            // Audit log
            $this->logExecution($user, $company, $toolName, $input, ['error' => $e->getMessage()], 'error');

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validate user has access to execute tool.
     */
    protected function validateToolAccess(AbstractTool $tool, User $user, ?Company $company): void
    {
        // Get allowed tools for user
        $allowedTools = $this->registry->getToolsForContext($user, $company);

        if (!isset($allowedTools[$tool->getName()])) {
            throw new \Exception("You don't have permission to use tool: {$tool->getName()}");
        }

        // Verify tenant access if company context
        if ($company && !$user->hasAccessToCompany($company->id)) {
            throw new \Exception('No access to this company');
        }
    }

    /**
     * Validate input against JSON schema (basic validation).
     */
    protected function validateInput(array $input, array $schema): void
    {
        if (!isset($schema['properties'])) {
            return;
        }

        $required = $schema['required'] ?? [];

        // Check required fields
        foreach ($required as $field) {
            if (!array_key_exists($field, $input)) {
                throw new \Exception("Missing required field: {$field}");
            }
        }

        // Basic type checking
        foreach ($input as $key => $value) {
            if (!isset($schema['properties'][$key])) {
                continue; // Allow extra fields
            }

            $expectedType = $schema['properties'][$key]['type'] ?? null;
            if ($expectedType && !$this->validateType($value, $expectedType)) {
                throw new \Exception("Invalid type for field '{$key}': expected {$expectedType}");
            }
        }
    }

    /**
     * Basic type validation.
     */
    protected function validateType($value, string $type): bool
    {
        return match($type) {
            'string' => is_string($value),
            'number', 'integer' => is_numeric($value),
            'boolean' => is_bool($value),
            'array' => is_array($value),
            'object' => is_array($value),
            default => true,
        };
    }

    /**
     * Log tool execution to audit log.
     */
    protected function logExecution(
        User $user,
        ?Company $company,
        string $toolName,
        array $input,
        array $result,
        string $status
    ): void {
        AuditLog::create([
            'user_id' => $user->id,
            'company_id' => $company?->id,
            'action' => 'ai_tool_execution',
            'auditable_type' => 'AITool',
            'auditable_id' => null,
            'metadata' => [
                'tool_name' => $toolName,
                'input' => $input,
                'result' => $result,
                'status' => $status,
            ],
        ]);
    }
}
