<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingProgress extends Model
{
    protected $table = 'onboarding_progress';

    protected $fillable = [
        'user_id',
        'company_id',
        'role',
        'goals',
        'experience_level',
        'completed_steps',
        'progress_percentage',
        'tour_completed',
        'tour_steps_seen',
        'tour_started_at',
        'tour_completed_at',
        'onboarding_completed',
        'completed_at',
        'skipped',
        'metadata',
    ];

    protected $casts = [
        'goals' => 'array',
        'completed_steps' => 'array',
        'tour_steps_seen' => 'array',
        'tour_completed' => 'boolean',
        'onboarding_completed' => 'boolean',
        'skipped' => 'boolean',
        'tour_started_at' => 'datetime',
        'tour_completed_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected $attributes = [
        'completed_steps' => '[]',
        'tour_steps_seen' => '[]',
        'metadata' => '{}',
        'progress_percentage' => 0,
        'tour_completed' => false,
        'onboarding_completed' => false,
        'skipped' => false,
    ];

    /**
     * Available onboarding steps (checklist items)
     */
    public const STEPS = [
        'survey_completed' => [
            'title' => 'Compléter le questionnaire',
            'description' => 'Personnalisez votre expérience',
            'points' => 10,
            'icon' => '📋',
        ],
        'profile_completed' => [
            'title' => 'Compléter votre profil',
            'description' => 'Nom, email, photo',
            'points' => 10,
            'icon' => '👤',
        ],
        'company_setup' => [
            'title' => 'Configurer votre entreprise',
            'description' => 'Informations légales et bancaires',
            'points' => 15,
            'icon' => '🏢',
        ],
        'first_partner' => [
            'title' => 'Créer votre premier client',
            'description' => 'Ajoutez un client ou fournisseur',
            'points' => 10,
            'icon' => '🤝',
        ],
        'first_product' => [
            'title' => 'Ajouter un produit/service',
            'description' => 'Créez votre catalogue',
            'points' => 10,
            'icon' => '📦',
        ],
        'first_invoice' => [
            'title' => 'Créer votre première facture',
            'description' => 'Émettez votre première facture',
            'points' => 20,
            'icon' => '📄',
        ],
        'bank_connected' => [
            'title' => 'Connecter votre banque',
            'description' => 'Open Banking PSD2',
            'points' => 15,
            'icon' => '🏦',
        ],
        'tour_completed' => [
            'title' => 'Terminer le tour guidé',
            'description' => 'Découvrez les fonctionnalités',
            'points' => 10,
            'icon' => '🎯',
        ],
    ];

    /**
     * Get the user who owns the progress.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the company context.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Mark a step as completed.
     */
    public function completeStep(string $step): void
    {
        if (!in_array($step, $this->completed_steps)) {
            $steps = $this->completed_steps;
            $steps[] = $step;

            $this->update([
                'completed_steps' => $steps,
                'progress_percentage' => $this->calculateProgress($steps),
            ]);

            // Check if onboarding is complete
            if ($this->progress_percentage >= 100 && !$this->onboarding_completed) {
                $this->update([
                    'onboarding_completed' => true,
                    'completed_at' => now(),
                ]);
            }
        }
    }

    /**
     * Check if a step is completed.
     */
    public function isStepCompleted(string $step): bool
    {
        return in_array($step, $this->completed_steps);
    }

    /**
     * Get remaining steps.
     */
    public function getRemainingSteps(): array
    {
        return array_filter(
            array_keys(self::STEPS),
            fn($step) => !$this->isStepCompleted($step)
        );
    }

    /**
     * Calculate progress percentage.
     */
    protected function calculateProgress(array $completedSteps): int
    {
        $totalPoints = array_sum(array_column(self::STEPS, 'points'));
        $earnedPoints = array_sum(array_map(
            fn($step) => self::STEPS[$step]['points'] ?? 0,
            $completedSteps
        ));

        return min(100, (int) (($earnedPoints / $totalPoints) * 100));
    }

    /**
     * Get next recommended step.
     */
    public function getNextStep(): ?array
    {
        $remaining = $this->getRemainingSteps();

        if (empty($remaining)) {
            return null;
        }

        // Prioritize in logical order
        $priority = [
            'survey_completed',
            'profile_completed',
            'company_setup',
            'tour_completed',
            'first_partner',
            'first_product',
            'first_invoice',
            'bank_connected',
        ];

        foreach ($priority as $step) {
            if (in_array($step, $remaining)) {
                return [
                    'key' => $step,
                    ...self::STEPS[$step]
                ];
            }
        }

        return null;
    }

    /**
     * Mark tour step as seen.
     */
    public function markTourStepSeen(string $stepId): void
    {
        if (!in_array($stepId, $this->tour_steps_seen)) {
            $steps = $this->tour_steps_seen;
            $steps[] = $stepId;

            $this->update([
                'tour_steps_seen' => $steps,
            ]);
        }
    }

    /**
     * Complete the tour.
     */
    public function completeTour(): void
    {
        $this->update([
            'tour_completed' => true,
            'tour_completed_at' => now(),
        ]);

        $this->completeStep('tour_completed');
    }

    /**
     * Skip onboarding.
     */
    public function skip(): void
    {
        $this->update([
            'skipped' => true,
            'onboarding_completed' => true,
            'completed_at' => now(),
        ]);
    }

    /**
     * Get formatted progress for display.
     */
    public function getProgressAttribute(): array
    {
        return [
            'percentage' => $this->progress_percentage,
            'completed_count' => count($this->completed_steps),
            'total_count' => count(self::STEPS),
            'is_complete' => $this->onboarding_completed,
            'next_step' => $this->getNextStep(),
        ];
    }
}
