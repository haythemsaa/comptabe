<?php

namespace App\Services\AI;

use App\Models\ChartOfAccount;
use App\Models\VatCode;
use App\Models\InvoiceLine;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class IntelligentCategorizationService
{
    protected array $categoryPatterns;
    protected array $accountMappings;
    protected array $vatCodeRules;

    public function __construct()
    {
        $this->initializePatterns();
    }

    /**
     * Categorize an expense description using ML-like pattern matching
     */
    public function categorize(string $description, array $context = []): array
    {
        $description = $this->normalizeText($description);

        // Try learned patterns first (from user corrections)
        $learnedCategory = $this->checkLearnedPatterns($description);
        if ($learnedCategory) {
            return $learnedCategory;
        }

        // Try rule-based categorization
        $category = $this->matchPatterns($description);

        // Get suggested account and VAT code
        $account = $this->suggestAccount($category, $description, $context);
        $vatCode = $this->suggestVatCode($category, $description, $context);

        return [
            'category' => $category,
            'subcategory' => $this->getSubcategory($category, $description),
            'account' => $account,
            'vat_code' => $vatCode,
            'confidence' => $this->calculateConfidence($description, $category),
            'alternatives' => $this->getAlternatives($description, $category),
        ];
    }

    /**
     * Learn from user corrections
     */
    public function learn(string $description, array $correction): void
    {
        $key = $this->generatePatternKey($description);

        $learnedPatterns = Cache::get('expense_patterns_' . auth()->user()->current_company_id, []);
        $learnedPatterns[$key] = [
            'original' => $description,
            'category' => $correction['category'] ?? null,
            'account_id' => $correction['account_id'] ?? null,
            'vat_code_id' => $correction['vat_code_id'] ?? null,
            'learned_at' => now()->toIso8601String(),
            'uses' => ($learnedPatterns[$key]['uses'] ?? 0) + 1,
        ];

        Cache::put('expense_patterns_' . auth()->user()->current_company_id, $learnedPatterns, now()->addYear());

        // Also save to database for persistence
        $this->saveLearnedPattern($description, $correction);
    }

    /**
     * Bulk categorize multiple items
     */
    public function categorizeBulk(array $items): array
    {
        return array_map(function ($item) {
            $description = is_array($item) ? ($item['description'] ?? '') : $item;
            $context = is_array($item) ? $item : [];

            return [
                'original' => $description,
                'categorization' => $this->categorize($description, $context),
            ];
        }, $items);
    }

    /**
     * Get category suggestions for autocomplete
     */
    public function getSuggestions(string $partial): array
    {
        $partial = $this->normalizeText($partial);
        $suggestions = [];

        foreach ($this->categoryPatterns as $category => $patterns) {
            foreach ($patterns['keywords'] as $keyword) {
                if (Str::contains($keyword, $partial) || Str::contains($partial, $keyword)) {
                    $suggestions[] = [
                        'category' => $category,
                        'label' => $patterns['label'],
                        'account' => $this->suggestAccount($category, $partial, []),
                        'match_score' => similar_text($partial, $keyword),
                    ];
                    break;
                }
            }
        }

        // Sort by match score
        usort($suggestions, fn($a, $b) => $b['match_score'] <=> $a['match_score']);

        return array_slice($suggestions, 0, 5);
    }

    /**
     * Analyze spending patterns for a company
     */
    public function analyzeSpendingPatterns(int $companyId, string $period = 'year'): array
    {
        $startDate = match ($period) {
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->subYear(),
        };

        $expenses = InvoiceLine::whereHas('invoice', function ($q) use ($companyId, $startDate) {
            $q->where('company_id', $companyId)
              ->where('type', 'in')
              ->where('invoice_date', '>=', $startDate);
        })->get();

        $patterns = [];
        $categoryTotals = [];
        $monthlyTrends = [];

        foreach ($expenses as $expense) {
            $cat = $this->categorize($expense->description);
            $category = $cat['category'];
            $month = $expense->invoice->invoice_date->format('Y-m');

            $categoryTotals[$category] = ($categoryTotals[$category] ?? 0) + $expense->total_excl_vat;

            if (!isset($monthlyTrends[$category])) {
                $monthlyTrends[$category] = [];
            }
            $monthlyTrends[$category][$month] = ($monthlyTrends[$category][$month] ?? 0) + $expense->total_excl_vat;
        }

        // Detect anomalies
        $anomalies = $this->detectSpendingAnomalies($monthlyTrends);

        // Calculate predictions
        $predictions = $this->predictNextMonth($monthlyTrends);

        return [
            'category_totals' => $categoryTotals,
            'monthly_trends' => $monthlyTrends,
            'anomalies' => $anomalies,
            'predictions' => $predictions,
            'top_categories' => array_slice(
                array_keys(array_sort($categoryTotals, fn($v) => -$v)),
                0, 5
            ),
        ];
    }

    /**
     * Initialize category patterns
     */
    protected function initializePatterns(): void
    {
        $this->categoryPatterns = [
            'office_supplies' => [
                'label' => 'Fournitures de bureau',
                'keywords' => ['papier', 'stylo', 'classeur', 'bureau', 'fourniture', 'toner', 'cartouche', 'encre', 'agrafeuse', 'papeterie'],
                'account_code' => '6110',
                'vat_code' => 'S21',
            ],
            'it_equipment' => [
                'label' => 'Matériel informatique',
                'keywords' => ['ordinateur', 'laptop', 'écran', 'clavier', 'souris', 'serveur', 'disque', 'ssd', 'ram', 'imprimante', 'scanner', 'pc', 'mac', 'apple', 'dell', 'hp', 'lenovo'],
                'account_code' => '2400',
                'vat_code' => 'S21',
            ],
            'software' => [
                'label' => 'Logiciels et licences',
                'keywords' => ['licence', 'software', 'logiciel', 'abonnement', 'saas', 'microsoft', 'adobe', 'office 365', 'google workspace', 'antivirus', 'subscription'],
                'account_code' => '6120',
                'vat_code' => 'S21',
            ],
            'telecommunications' => [
                'label' => 'Télécommunications',
                'keywords' => ['téléphone', 'mobile', 'internet', 'fibre', 'proximus', 'orange', 'base', 'telenet', 'voo', 'gsm', 'data', 'roaming'],
                'account_code' => '6130',
                'vat_code' => 'S21',
            ],
            'rent' => [
                'label' => 'Loyer',
                'keywords' => ['loyer', 'location', 'bail', 'rent', 'huur', 'bureaux'],
                'account_code' => '6100',
                'vat_code' => 'S21',
            ],
            'utilities' => [
                'label' => 'Charges (eau, gaz, électricité)',
                'keywords' => ['électricité', 'gaz', 'eau', 'energie', 'engie', 'luminus', 'edf', 'total', 'eneco', 'mazout', 'chauffage'],
                'account_code' => '6110',
                'vat_code' => 'S21',
            ],
            'insurance' => [
                'label' => 'Assurances',
                'keywords' => ['assurance', 'verzekering', 'insurance', 'prime', 'axa', 'ag', 'ethias', 'belfius', 'kbc'],
                'account_code' => '6140',
                'vat_code' => 'S0',
            ],
            'professional_services' => [
                'label' => 'Services professionnels',
                'keywords' => ['comptable', 'avocat', 'consultant', 'conseil', 'expert', 'honoraires', 'audit', 'juridique', 'notaire', 'huissier'],
                'account_code' => '6150',
                'vat_code' => 'S21',
            ],
            'marketing' => [
                'label' => 'Marketing et publicité',
                'keywords' => ['publicité', 'marketing', 'facebook', 'google ads', 'linkedin', 'campagne', 'flyer', 'brochure', 'affiche', 'sponsor'],
                'account_code' => '6160',
                'vat_code' => 'S21',
            ],
            'travel' => [
                'label' => 'Déplacements',
                'keywords' => ['train', 'sncb', 'nmbs', 'avion', 'billet', 'transport', 'taxi', 'uber', 'parking', 'péage', 'carburant', 'essence', 'diesel'],
                'account_code' => '6170',
                'vat_code' => 'S21',
            ],
            'meals_entertainment' => [
                'label' => 'Repas et représentation',
                'keywords' => ['restaurant', 'repas', 'lunch', 'dîner', 'catering', 'traiteur', 'réception', 'client'],
                'account_code' => '6180',
                'vat_code' => 'S21', // Only 69% deductible
            ],
            'vehicle' => [
                'label' => 'Véhicule',
                'keywords' => ['voiture', 'auto', 'leasing', 'carburant', 'essence', 'diesel', 'entretien', 'pneu', 'réparation', 'garage', 'contrôle technique'],
                'account_code' => '6170',
                'vat_code' => 'S21', // Partial deduction
            ],
            'training' => [
                'label' => 'Formation',
                'keywords' => ['formation', 'cours', 'séminaire', 'conférence', 'workshop', 'certification', 'training', 'e-learning'],
                'account_code' => '6190',
                'vat_code' => 'S21',
            ],
            'banking' => [
                'label' => 'Frais bancaires',
                'keywords' => ['banque', 'bank', 'frais', 'commission', 'virement', 'carte', 'isabel'],
                'account_code' => '6500',
                'vat_code' => 'S0',
            ],
            'raw_materials' => [
                'label' => 'Matières premières',
                'keywords' => ['matière', 'matériau', 'stock', 'composant', 'pièce'],
                'account_code' => '6000',
                'vat_code' => 'S21',
            ],
            'merchandise' => [
                'label' => 'Marchandises',
                'keywords' => ['marchandise', 'produit', 'article', 'revente'],
                'account_code' => '6040',
                'vat_code' => 'S21',
            ],
            'maintenance' => [
                'label' => 'Entretien et réparations',
                'keywords' => ['entretien', 'réparation', 'maintenance', 'nettoyage', 'cleaning'],
                'account_code' => '6110',
                'vat_code' => 'S21',
            ],
            'subscriptions' => [
                'label' => 'Abonnements et cotisations',
                'keywords' => ['abonnement', 'cotisation', 'membership', 'adhésion', 'revue', 'journal'],
                'account_code' => '6190',
                'vat_code' => 'S21',
            ],
        ];

        $this->accountMappings = Cache::remember('account_mappings', 3600, function () {
            try {
                return ChartOfAccount::where('is_group', false)
                    ->pluck('id', 'account_number')
                    ->toArray();
            } catch (\Exception $e) {
                // Return empty array if table doesn't exist yet
                return [];
            }
        });
    }

    /**
     * Normalize text for matching
     */
    protected function normalizeText(string $text): string
    {
        $text = Str::lower($text);
        $text = preg_replace('/[^a-z0-9àâäéèêëïîôùûüç\s]/u', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    /**
     * Check learned patterns
     */
    protected function checkLearnedPatterns(string $description): ?array
    {
        $patterns = Cache::get('expense_patterns_' . auth()->user()->current_company_id, []);

        foreach ($patterns as $key => $pattern) {
            if ($this->matchesSimilar($description, $pattern['original'])) {
                return [
                    'category' => $pattern['category'],
                    'account' => $pattern['account_id'],
                    'vat_code' => $pattern['vat_code_id'],
                    'confidence' => min(0.95, 0.7 + ($pattern['uses'] * 0.05)),
                    'source' => 'learned',
                ];
            }
        }

        return null;
    }

    /**
     * Match description against patterns
     */
    protected function matchPatterns(string $description): string
    {
        $bestMatch = 'other';
        $bestScore = 0;

        foreach ($this->categoryPatterns as $category => $config) {
            $score = 0;

            foreach ($config['keywords'] as $keyword) {
                if (Str::contains($description, $keyword)) {
                    $score += strlen($keyword);
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $category;
            }
        }

        return $bestMatch;
    }

    /**
     * Suggest account based on category
     */
    protected function suggestAccount(string $category, string $description, array $context): ?int
    {
        // First check if we have a learned pattern
        if (isset($context['account_id'])) {
            return $context['account_id'];
        }

        // Get from category patterns
        $accountCode = $this->categoryPatterns[$category]['account_code'] ?? '6190';

        return $this->accountMappings[$accountCode] ?? null;
    }

    /**
     * Suggest VAT code
     */
    protected function suggestVatCode(string $category, string $description, array $context): ?string
    {
        // Check for intracommunity keywords
        $intracomKeywords = ['intracommunautaire', 'ue', 'eu', 'europe', 'intra-'];
        foreach ($intracomKeywords as $keyword) {
            if (Str::contains($description, $keyword)) {
                return 'IC0';
            }
        }

        // Check for reverse charge
        $reverseChargeKeywords = ['autoliquidation', 'cocontractant', 'reverse charge'];
        foreach ($reverseChargeKeywords as $keyword) {
            if (Str::contains($description, $keyword)) {
                return 'CC';
            }
        }

        return $this->categoryPatterns[$category]['vat_code'] ?? 'S21';
    }

    /**
     * Calculate confidence score
     */
    protected function calculateConfidence(string $description, string $category): float
    {
        if ($category === 'other') {
            return 0.3;
        }

        $matchedKeywords = 0;
        $keywords = $this->categoryPatterns[$category]['keywords'] ?? [];

        foreach ($keywords as $keyword) {
            if (Str::contains($description, $keyword)) {
                $matchedKeywords++;
            }
        }

        $baseConfidence = 0.5;
        $keywordBonus = min(0.4, $matchedKeywords * 0.1);

        return $baseConfidence + $keywordBonus;
    }

    /**
     * Get subcategory
     */
    protected function getSubcategory(string $category, string $description): ?string
    {
        $subcategories = [
            'it_equipment' => [
                'computer' => ['ordinateur', 'laptop', 'pc', 'mac'],
                'peripheral' => ['écran', 'clavier', 'souris', 'imprimante'],
                'storage' => ['disque', 'ssd', 'nas', 'backup'],
            ],
            'vehicle' => [
                'fuel' => ['carburant', 'essence', 'diesel', 'mazout'],
                'maintenance' => ['entretien', 'révision', 'pneu'],
                'repair' => ['réparation', 'garage'],
            ],
        ];

        if (!isset($subcategories[$category])) {
            return null;
        }

        foreach ($subcategories[$category] as $subcat => $keywords) {
            foreach ($keywords as $keyword) {
                if (Str::contains($description, $keyword)) {
                    return $subcat;
                }
            }
        }

        return null;
    }

    /**
     * Get alternative categorizations
     */
    protected function getAlternatives(string $description, string $currentCategory): array
    {
        $alternatives = [];

        foreach ($this->categoryPatterns as $category => $config) {
            if ($category === $currentCategory) {
                continue;
            }

            $score = 0;
            foreach ($config['keywords'] as $keyword) {
                if (Str::contains($description, $keyword)) {
                    $score++;
                }
            }

            if ($score > 0) {
                $alternatives[] = [
                    'category' => $category,
                    'label' => $config['label'],
                    'score' => $score,
                ];
            }
        }

        usort($alternatives, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($alternatives, 0, 3);
    }

    /**
     * Check if descriptions are similar
     */
    protected function matchesSimilar(string $a, string $b): bool
    {
        similar_text($a, $b, $percent);
        return $percent > 80;
    }

    /**
     * Detect spending anomalies
     */
    protected function detectSpendingAnomalies(array $monthlyTrends): array
    {
        $anomalies = [];

        foreach ($monthlyTrends as $category => $months) {
            $values = array_values($months);

            if (count($values) < 3) {
                continue;
            }

            $mean = array_sum($values) / count($values);
            $variance = array_sum(array_map(fn($v) => pow($v - $mean, 2), $values)) / count($values);
            $stdDev = sqrt($variance);

            foreach ($months as $month => $value) {
                $zScore = $stdDev > 0 ? ($value - $mean) / $stdDev : 0;

                if (abs($zScore) > 2) {
                    $anomalies[] = [
                        'category' => $category,
                        'month' => $month,
                        'value' => $value,
                        'expected' => round($mean, 2),
                        'deviation' => round($zScore, 2),
                        'type' => $zScore > 0 ? 'spike' : 'drop',
                        'severity' => abs($zScore) > 3 ? 'high' : 'medium',
                    ];
                }
            }
        }

        return $anomalies;
    }

    /**
     * Predict next month spending
     */
    protected function predictNextMonth(array $monthlyTrends): array
    {
        $predictions = [];

        foreach ($monthlyTrends as $category => $months) {
            $values = array_values($months);

            if (count($values) < 3) {
                $predictions[$category] = [
                    'predicted' => end($values) ?: 0,
                    'confidence' => 'low',
                    'method' => 'last_value',
                ];
                continue;
            }

            // Simple linear regression
            $n = count($values);
            $x = range(1, $n);
            $sumX = array_sum($x);
            $sumY = array_sum($values);
            $sumXY = array_sum(array_map(fn($xi, $yi) => $xi * $yi, $x, $values));
            $sumX2 = array_sum(array_map(fn($xi) => $xi * $xi, $x));

            $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
            $intercept = ($sumY - $slope * $sumX) / $n;

            $predicted = $slope * ($n + 1) + $intercept;

            $predictions[$category] = [
                'predicted' => max(0, round($predicted, 2)),
                'trend' => $slope > 0 ? 'increasing' : ($slope < 0 ? 'decreasing' : 'stable'),
                'confidence' => $n > 6 ? 'high' : ($n > 3 ? 'medium' : 'low'),
                'method' => 'linear_regression',
            ];
        }

        return $predictions;
    }

    /**
     * Save learned pattern to database
     */
    protected function saveLearnedPattern(string $description, array $correction): void
    {
        // This would save to a learned_patterns table
        // Implementation depends on your database structure
    }

    /**
     * Generate pattern key for caching
     */
    protected function generatePatternKey(string $description): string
    {
        return md5($this->normalizeText($description));
    }
}
