<?php

namespace App\Services\AI;

use App\Models\Company;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FinancialAnalysisService
{
    protected ?string $apiKey;
    protected string $model;
    protected string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('ai.claude.api_key');
        $this->model = config('ai.claude.model', 'claude-3-5-sonnet-20241022');
        $this->apiUrl = config('ai.claude.base_url', 'https://api.anthropic.com/v1');
    }

    /**
     * Analyze balance sheet and P&L with AI.
     */
    public function analyzeFinancialReports(array $balanceSheet, array $profitLoss, Company $company): array
    {
        // Calculate financial ratios first
        $ratios = $this->calculateFinancialRatios($balanceSheet, $profitLoss);

        // Detect anomalies
        $anomalies = $this->detectAnomalies($balanceSheet, $profitLoss, $ratios);

        // If Claude API is available, get AI insights
        $aiInsights = null;
        if ($this->apiKey) {
            $aiInsights = $this->getAIInsights($balanceSheet, $profitLoss, $ratios, $anomalies, $company);
        }

        return [
            'ratios' => $ratios,
            'anomalies' => $anomalies,
            'health_score' => $this->calculateHealthScore($ratios, $anomalies),
            'recommendations' => $this->generateRecommendations($ratios, $anomalies),
            'ai_insights' => $aiInsights,
            'trends' => $this->analyzeTrends($profitLoss),
        ];
    }

    /**
     * Calculate key financial ratios.
     */
    protected function calculateFinancialRatios(array $balanceSheet, array $profitLoss): array
    {
        $assets = $balanceSheet['assets']['total'] ?? 0;
        $liabilities = $balanceSheet['liabilities']['total'] ?? 0;
        $equity = $balanceSheet['liabilities']['categories']['equity']['total'] ?? 0;

        $revenue = $profitLoss['revenue']['total'] ?? 0;
        $expenses = $profitLoss['expenses']['total'] ?? 0;
        $netResult = $profitLoss['net_result'] ?? 0;
        $operatingResult = $profitLoss['operating_result'] ?? 0;

        // Current assets and liabilities for liquidity ratios
        $currentAssets = $balanceSheet['assets']['categories']['current']['total'] ?? 0;
        $currentLiabilities = $balanceSheet['liabilities']['categories']['debts']['total'] ?? 0;

        $ratios = [];

        // Solvency ratios
        $ratios['equity_ratio'] = [
            'value' => $assets > 0 ? round(($equity / $assets) * 100, 2) : 0,
            'label' => 'Ratio de fonds propres',
            'unit' => '%',
            'benchmark' => 30, // Minimum 30% recommended
            'interpretation' => 'Mesure l\'autonomie financière',
        ];

        $ratios['debt_ratio'] = [
            'value' => $assets > 0 ? round(($liabilities / $assets) * 100, 2) : 0,
            'label' => 'Ratio d\'endettement',
            'unit' => '%',
            'benchmark' => 70, // Maximum 70% recommended
            'interpretation' => 'Mesure le niveau d\'endettement',
        ];

        // Liquidity ratios
        $ratios['current_ratio'] = [
            'value' => $currentLiabilities > 0 ? round($currentAssets / $currentLiabilities, 2) : 0,
            'label' => 'Ratio de liquidité générale',
            'unit' => 'x',
            'benchmark' => 1.5, // Minimum 1.5 recommended
            'interpretation' => 'Capacité à rembourser les dettes à court terme',
        ];

        // Profitability ratios
        $ratios['net_margin'] = [
            'value' => $revenue > 0 ? round(($netResult / $revenue) * 100, 2) : 0,
            'label' => 'Marge nette',
            'unit' => '%',
            'benchmark' => 10, // Varies by industry
            'interpretation' => 'Rentabilité finale après toutes les charges',
        ];

        $ratios['operating_margin'] = [
            'value' => $revenue > 0 ? round(($operatingResult / $revenue) * 100, 2) : 0,
            'label' => 'Marge opérationnelle',
            'unit' => '%',
            'benchmark' => 15,
            'interpretation' => 'Rentabilité des activités opérationnelles',
        ];

        $ratios['roe'] = [
            'value' => $equity > 0 ? round(($netResult / $equity) * 100, 2) : 0,
            'label' => 'Rentabilité des capitaux propres (ROE)',
            'unit' => '%',
            'benchmark' => 15,
            'interpretation' => 'Rentabilité pour les actionnaires',
        ];

        $ratios['roa'] = [
            'value' => $assets > 0 ? round(($netResult / $assets) * 100, 2) : 0,
            'label' => 'Rentabilité des actifs (ROA)',
            'unit' => '%',
            'benchmark' => 5,
            'interpretation' => 'Efficacité d\'utilisation des actifs',
        ];

        return $ratios;
    }

    /**
     * Detect financial anomalies and risks.
     */
    protected function detectAnomalies(array $balanceSheet, array $profitLoss, array $ratios): array
    {
        $anomalies = [];

        // Check if balance sheet is balanced
        if (isset($balanceSheet['balanced']) && !$balanceSheet['balanced']) {
            $anomalies[] = [
                'severity' => 'critical',
                'type' => 'accounting_error',
                'message' => 'Le bilan n\'est pas équilibré (Actif ≠ Passif)',
                'recommendation' => 'Vérifiez les écritures comptables pour corriger ce déséquilibre.',
            ];
        }

        // Check negative equity
        $equity = $balanceSheet['liabilities']['categories']['equity']['total'] ?? 0;
        if ($equity < 0) {
            $anomalies[] = [
                'severity' => 'critical',
                'type' => 'negative_equity',
                'message' => 'Capitaux propres négatifs (' . number_format($equity, 2) . ' €)',
                'recommendation' => 'Situation critique! Envisagez un apport en capital ou une restructuration.',
            ];
        }

        // Check low liquidity
        if ($ratios['current_ratio']['value'] < 1) {
            $anomalies[] = [
                'severity' => 'warning',
                'type' => 'low_liquidity',
                'message' => 'Liquidité insuffisante (ratio: ' . $ratios['current_ratio']['value'] . ')',
                'recommendation' => 'Risque de difficulté à payer les dettes à court terme. Améliorer la trésorerie.',
            ];
        }

        // Check high debt
        if ($ratios['debt_ratio']['value'] > 80) {
            $anomalies[] = [
                'severity' => 'warning',
                'type' => 'high_debt',
                'message' => 'Endettement élevé (' . $ratios['debt_ratio']['value'] . '%)',
                'recommendation' => 'Réduire l\'endettement ou augmenter les fonds propres.',
            ];
        }

        // Check profitability
        $netResult = $profitLoss['net_result'] ?? 0;
        if ($netResult < 0) {
            $anomalies[] = [
                'severity' => $netResult < -50000 ? 'critical' : 'warning',
                'type' => 'loss',
                'message' => 'Perte de ' . number_format(abs($netResult), 2) . ' €',
                'recommendation' => 'Analyser les charges et optimiser les revenus.',
            ];
        }

        // Check low profitability
        if ($netResult > 0 && $ratios['net_margin']['value'] < 5) {
            $anomalies[] = [
                'severity' => 'info',
                'type' => 'low_margin',
                'message' => 'Marge nette faible (' . $ratios['net_margin']['value'] . '%)',
                'recommendation' => 'Optimiser les coûts ou augmenter les prix.',
            ];
        }

        return $anomalies;
    }

    /**
     * Calculate overall financial health score (0-100).
     */
    protected function calculateHealthScore(array $ratios, array $anomalies): array
    {
        $score = 100;

        // Deduct points for anomalies
        foreach ($anomalies as $anomaly) {
            $score -= match($anomaly['severity']) {
                'critical' => 25,
                'warning' => 15,
                'info' => 5,
                default => 0,
            };
        }

        // Deduct points for poor ratios
        if ($ratios['equity_ratio']['value'] < 20) $score -= 10;
        if ($ratios['current_ratio']['value'] < 1) $score -= 15;
        if ($ratios['net_margin']['value'] < 0) $score -= 20;
        if ($ratios['debt_ratio']['value'] > 80) $score -= 10;

        $score = max(0, min(100, $score));

        $status = match(true) {
            $score >= 80 => 'Excellent',
            $score >= 60 => 'Bon',
            $score >= 40 => 'Moyen',
            $score >= 20 => 'Faible',
            default => 'Critique',
        };

        $color = match(true) {
            $score >= 80 => 'success',
            $score >= 60 => 'info',
            $score >= 40 => 'warning',
            default => 'danger',
        };

        return [
            'score' => $score,
            'status' => $status,
            'color' => $color,
        ];
    }

    /**
     * Generate recommendations based on analysis.
     */
    protected function generateRecommendations(array $ratios, array $anomalies): array
    {
        $recommendations = [];

        // Recommendations from anomalies
        foreach ($anomalies as $anomaly) {
            if (isset($anomaly['recommendation'])) {
                $recommendations[] = [
                    'priority' => $anomaly['severity'],
                    'category' => $anomaly['type'],
                    'message' => $anomaly['recommendation'],
                ];
            }
        }

        // Additional recommendations based on ratios
        if ($ratios['current_ratio']['value'] > 3) {
            $recommendations[] = [
                'priority' => 'info',
                'category' => 'optimization',
                'message' => 'Liquidité excédentaire. Envisagez d\'investir les excédents de trésorerie.',
            ];
        }

        if ($ratios['net_margin']['value'] > 20) {
            $recommendations[] = [
                'priority' => 'info',
                'category' => 'growth',
                'message' => 'Excellente rentabilité! Moment propice pour investir dans la croissance.',
            ];
        }

        return $recommendations;
    }

    /**
     * Analyze trends (requires historical data - placeholder for now).
     */
    protected function analyzeTrends(array $profitLoss): array
    {
        return [
            'revenue_trend' => 'stable', // Placeholder
            'margin_trend' => 'stable',
            'message' => 'Analyse des tendances nécessite plusieurs périodes de données.',
        ];
    }

    /**
     * Get AI insights from Claude API.
     */
    protected function getAIInsights(array $balanceSheet, array $profitLoss, array $ratios, array $anomalies, Company $company): ?array
    {
        if (!$this->apiKey) {
            return null;
        }

        try {
            $prompt = $this->buildAnalysisPrompt($balanceSheet, $profitLoss, $ratios, $anomalies, $company);

            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->timeout(30)->post("{$this->apiUrl}/messages", [
                'model' => $this->model,
                'max_tokens' => 2048,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['content'][0]['text'] ?? null;

                return [
                    'analysis' => $content,
                    'model' => $this->model,
                    'generated_at' => now()->toIso8601String(),
                ];
            }

            Log::warning('Claude API error', ['response' => $response->body()]);
            return null;

        } catch (\Exception $e) {
            Log::error('Financial Analysis AI Error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Build prompt for Claude API.
     */
    protected function buildAnalysisPrompt(array $balanceSheet, array $profitLoss, array $ratios, array $anomalies, Company $company): string
    {
        $assets = $balanceSheet['assets']['total'] ?? 0;
        $liabilities = $balanceSheet['liabilities']['total'] ?? 0;
        $revenue = $profitLoss['revenue']['total'] ?? 0;
        $expenses = $profitLoss['expenses']['total'] ?? 0;
        $netResult = $profitLoss['net_result'] ?? 0;

        $anomaliesText = '';
        foreach ($anomalies as $anomaly) {
            $anomaliesText .= "- [{$anomaly['severity']}] {$anomaly['message']}\n";
        }

        $ratiosText = '';
        foreach ($ratios as $key => $ratio) {
            $ratiosText .= "- {$ratio['label']}: {$ratio['value']}{$ratio['unit']} (benchmark: {$ratio['benchmark']}{$ratio['unit']})\n";
        }

        return <<<PROMPT
Vous êtes un expert-comptable belge spécialisé dans l'analyse financière. Analysez les données financières suivantes et fournissez une analyse complète et des recommandations stratégiques.

**Entreprise:** {$company->name}
**Secteur:** PME Belge

**BILAN:**
- Total Actif: {$assets} €
- Total Passif: {$liabilities} €

**COMPTE DE RÉSULTAT:**
- Chiffre d'affaires: {$revenue} €
- Charges totales: {$expenses} €
- Résultat net: {$netResult} €

**RATIOS FINANCIERS:**
{$ratiosText}

**ANOMALIES DÉTECTÉES:**
{$anomaliesText}

**CONSIGNES:**
1. Fournissez une analyse de la santé financière globale
2. Identifiez les forces et faiblesses principales
3. Donnez 3-5 recommandations concrètes et actionnables
4. Mentionnez les risques potentiels
5. Suggérez des opportunités d'amélioration

Répondez en français de manière concise et professionnelle (max 500 mots).
PROMPT;
    }
}
