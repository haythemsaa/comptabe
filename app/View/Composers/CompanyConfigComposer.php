<?php

namespace App\View\Composers;

use App\Models\Company;
use Illuminate\View\View;

class CompanyConfigComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $company = Company::current();

        if (!$company) {
            // Provide default values when no company is selected (e.g., superadmin without tenant)
            $view->with([
                'currentCompany' => null,
                'companyCurrency' => 'EUR',
                'companyCurrencySymbol' => '€',
                'companyDecimalPlaces' => 2,
                'companyVatRates' => [21, 12, 6, 0],
                'companyDefaultVatRate' => 21,
                'companySocialSecurityOrg' => 'ONSS',
                'companyIsTunisia' => false,
                'companyIsBelgium' => true,
                'companyCountryCode' => 'BE',
                'companyCountryName' => 'Belgique',
            ]);
            return;
        }

        // Share company country configuration with all views
        $view->with([
            'currentCompany' => $company,
            'companyCurrency' => $company->getCurrency(),
            'companyCurrencySymbol' => $company->getCurrencySymbol(),
            'companyDecimalPlaces' => $company->getDecimalPlaces(),
            'companyVatRates' => $company->getVatRates(),
            'companyDefaultVatRate' => $company->getDefaultVatRate(),
            'companySocialSecurityOrg' => $company->getSocialSecurityOrg(),
            'companyIsTunisia' => $company->isTunisia(),
            'companyIsBelgium' => $company->isBelgium(),
            'companyCountryCode' => $company->country_code,
            'companyCountryName' => $company->getCountryConfig()['name'] ?? 'Belgique',
        ]);
    }
}
