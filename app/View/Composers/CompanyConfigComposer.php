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
