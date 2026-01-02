<?php

namespace App\Services\Payroll;

use App\Models\Company;
use App\Services\Payroll\Calculators\BelgiumCalculator;
use App\Services\Payroll\Calculators\TunisiaCalculator;
use App\Services\Payroll\Calculators\FranceCalculator;
use Exception;

class PayrollCalculatorFactory
{
    /**
     * Create the appropriate payroll calculator based on country code
     *
     * @param string|Company $countryOrCompany Country code (BE, TN, FR) or Company model
     * @return PayrollCalculatorInterface
     * @throws Exception
     */
    public static function make(string|Company $countryOrCompany): PayrollCalculatorInterface
    {
        $countryCode = $countryOrCompany instanceof Company
            ? $countryOrCompany->country_code
            : $countryOrCompany;

        return match (strtoupper($countryCode)) {
            'BE' => new BelgiumCalculator(),
            'TN' => new TunisiaCalculator(),
            'FR' => new FranceCalculator(),
            default => throw new Exception("Payroll calculator not implemented for country: {$countryCode}"),
        };
    }

    /**
     * Get list of supported countries for payroll
     *
     * @return array
     */
    public static function getSupportedCountries(): array
    {
        return [
            'BE' => 'Belgique',
            'TN' => 'Tunisie',
            'FR' => 'France',
        ];
    }

    /**
     * Check if payroll is supported for a country
     *
     * @param string $countryCode
     * @return bool
     */
    public static function isSupported(string $countryCode): bool
    {
        return array_key_exists(strtoupper($countryCode), self::getSupportedCountries());
    }
}
