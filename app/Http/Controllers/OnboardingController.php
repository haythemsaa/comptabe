<?php

namespace App\Http\Controllers;

use App\Models\OnboardingProgress;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    /**
     * Get current user's onboarding status.
     */
    public function status(): JsonResponse
    {
        $user = Auth::user();
        $progress = $this->getOrCreateProgress($user);

        return response()->json([
            'tour_completed' => $progress->tour_completed,
            'skipped' => $progress->skipped,
            'show_tour' => !$progress->tour_completed && !$progress->skipped,
            'role' => $progress->role ?? 'freelance',
            'progress_percentage' => $progress->progress_percentage,
            'completed_steps' => $progress->completed_steps,
            'tour_steps_seen' => $progress->tour_steps_seen,
        ]);
    }

    /**
     * Mark tour as started.
     */
    public function startTour(): JsonResponse
    {
        $user = Auth::user();
        $progress = $this->getOrCreateProgress($user);

        $progress->update([
            'tour_started_at' => $progress->tour_started_at ?? now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tour démarré',
        ]);
    }

    /**
     * Track tour step as seen.
     */
    public function trackTourStep(Request $request, int $stepIndex): JsonResponse
    {
        $user = Auth::user();
        $progress = $this->getOrCreateProgress($user);

        $progress->markTourStepSeen((string) $stepIndex);

        return response()->json([
            'success' => true,
            'steps_seen' => $progress->tour_steps_seen,
        ]);
    }

    /**
     * Complete the tour.
     */
    public function completeTour(): JsonResponse
    {
        $user = Auth::user();
        $progress = $this->getOrCreateProgress($user);

        $progress->completeTour();

        // Award gamification points
        $user->addPoints(10, 'Tour guidé terminé');

        return response()->json([
            'success' => true,
            'message' => 'Tour complété ! +10 points',
            'progress_percentage' => $progress->fresh()->progress_percentage,
        ]);
    }

    /**
     * Save survey responses.
     */
    public function saveSurvey(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'role' => 'required|in:freelance,tpe,pme,comptable',
            'goals' => 'required|array|min:1',
            'goals.*' => 'in:facturation,suivi_depenses,conformite_fiscale,gestion_personnel,reporting,multi_societes',
            'experience_level' => 'required|in:debutant,intermediaire,expert',
        ]);

        $user = Auth::user();
        $progress = $this->getOrCreateProgress($user);

        $progress->update([
            'role' => $validated['role'],
            'goals' => $validated['goals'],
            'experience_level' => $validated['experience_level'],
        ]);

        // Mark survey step as completed
        $progress->completeStep('survey_completed');

        // Award gamification points
        $user->addPoints(10, 'Questionnaire complété');

        return response()->json([
            'success' => true,
            'message' => 'Préférences enregistrées ! +10 points',
            'progress_percentage' => $progress->fresh()->progress_percentage,
        ]);
    }

    /**
     * Skip onboarding.
     */
    public function skip(): JsonResponse
    {
        $user = Auth::user();
        $progress = $this->getOrCreateProgress($user);

        $progress->skip();

        return response()->json([
            'success' => true,
            'message' => 'Onboarding ignoré',
        ]);
    }

    /**
     * Get progress data for checklist display.
     */
    public function getProgress(): JsonResponse
    {
        $user = Auth::user();
        $progress = $this->getOrCreateProgress($user);

        $steps = collect(OnboardingProgress::STEPS)->map(function ($stepData, $stepKey) use ($progress) {
            return [
                'key' => $stepKey,
                'title' => $stepData['title'],
                'description' => $stepData['description'],
                'points' => $stepData['points'],
                'icon' => $stepData['icon'],
                'completed' => $progress->isStepCompleted($stepKey),
            ];
        })->values();

        return response()->json([
            'progress_percentage' => $progress->progress_percentage,
            'completed_count' => count($progress->completed_steps),
            'total_count' => count(OnboardingProgress::STEPS),
            'steps' => $steps,
            'next_step' => $progress->getNextStep(),
            'is_complete' => $progress->onboarding_completed,
        ]);
    }

    /**
     * Get or create onboarding progress for user.
     */
    private function getOrCreateProgress(User $user): OnboardingProgress
    {
        return OnboardingProgress::firstOrCreate(
            ['user_id' => $user->id],
            [
                'company_id' => $user->current_company_id,
                'role' => null,
                'goals' => [],
                'experience_level' => null,
                'completed_steps' => [],
                'progress_percentage' => 0,
                'tour_completed' => false,
                'tour_steps_seen' => [],
                'onboarding_completed' => false,
                'skipped' => false,
            ]
        );
    }
}
