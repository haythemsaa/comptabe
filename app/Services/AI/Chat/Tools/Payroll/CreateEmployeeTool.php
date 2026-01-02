<?php

namespace App\Services\AI\Chat\Tools\Payroll;

use App\Models\Employee;
use App\Services\AI\Chat\Tools\AbstractTool;
use App\Services\AI\Chat\Tools\ToolContext;

class CreateEmployeeTool extends AbstractTool
{
    public function getName(): string
    {
        return 'create_employee';
    }

    public function getDescription(): string
    {
        return 'Creates a new employee record with personal information and employment details. Use this when adding a new employee to the payroll system.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'first_name' => [
                    'type' => 'string',
                    'description' => 'First name',
                ],
                'last_name' => [
                    'type' => 'string',
                    'description' => 'Last name',
                ],
                'national_number' => [
                    'type' => 'string',
                    'description' => 'Belgian national number (numéro national) - 11 digits',
                ],
                'birth_date' => [
                    'type' => 'string',
                    'format' => 'date',
                    'description' => 'Date of birth (YYYY-MM-DD)',
                ],
                'gender' => [
                    'type' => 'string',
                    'enum' => ['M', 'F', 'X'],
                    'description' => 'Gender (M/F/X)',
                ],
                'email' => [
                    'type' => 'string',
                    'format' => 'email',
                    'description' => 'Email address',
                ],
                'phone' => [
                    'type' => 'string',
                    'description' => 'Phone number',
                ],
                'street' => [
                    'type' => 'string',
                    'description' => 'Street name',
                ],
                'house_number' => [
                    'type' => 'string',
                    'description' => 'House number',
                ],
                'postal_code' => [
                    'type' => 'string',
                    'description' => 'Postal code',
                ],
                'city' => [
                    'type' => 'string',
                    'description' => 'City',
                ],
                'iban' => [
                    'type' => 'string',
                    'description' => 'IBAN for salary payment',
                ],
                'hire_date' => [
                    'type' => 'string',
                    'format' => 'date',
                    'description' => 'Date of hire (YYYY-MM-DD)',
                ],
            ],
            'required' => ['first_name', 'last_name', 'national_number', 'birth_date', 'hire_date'],
        ];
    }

    public function requiresConfirmation(): bool
    {
        return true;
    }

    public function execute(array $input, ToolContext $context): array
    {
        // Validate tenant access
        $this->validateTenantAccess($context->user, $context->company);

        // Validate national number
        if (!Employee::validateNationalNumber($input['national_number'])) {
            return [
                'error' => 'Numéro national invalide. Format attendu : 11 chiffres avec checksum valide.',
                'example' => '85073003328 (exemple)',
            ];
        }

        // Check if employee with same national number already exists
        $existing = Employee::where('national_number', $input['national_number'])->first();
        if ($existing) {
            return [
                'error' => "Un employé avec ce numéro national existe déjà : {$existing->full_name}",
                'existing_employee' => [
                    'id' => $existing->id,
                    'name' => $existing->full_name,
                    'employee_number' => $existing->employee_number,
                ],
            ];
        }

        // Generate employee number
        $employeeNumber = Employee::generateEmployeeNumber($context->company);

        // Create employee
        $employee = Employee::create([
            'company_id' => $context->company->id,
            'employee_number' => $employeeNumber,
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'national_number' => $input['national_number'],
            'birth_date' => $input['birth_date'],
            'gender' => $input['gender'] ?? 'M',
            'email' => $input['email'] ?? null,
            'phone' => $input['phone'] ?? null,
            'street' => $input['street'] ?? '',
            'house_number' => $input['house_number'] ?? '',
            'postal_code' => $input['postal_code'] ?? '',
            'city' => $input['city'] ?? '',
            'country_code' => 'BE',
            'iban' => $input['iban'] ?? null,
            'hire_date' => $input['hire_date'],
            'status' => 'active',
        ]);

        return [
            'success' => true,
            'message' => "Employé créé avec succès : {$employee->full_name}",
            'employee' => [
                'id' => $employee->id,
                'employee_number' => $employee->employee_number,
                'full_name' => $employee->full_name,
                'national_number' => $employee->national_number,
                'hire_date' => $employee->hire_date->format('d/m/Y'),
                'age' => $employee->age,
                'email' => $employee->email,
                'iban' => $employee->iban,
            ],
            'next_steps' => [
                'Créer un contrat de travail pour cet employé',
                'Compléter les informations bancaires si manquantes',
                'Ajouter les bénéfices (voiture, chèques-repas, etc.)',
                'Déclarer via DIMONA dans les 24h',
            ],
        ];
    }
}
