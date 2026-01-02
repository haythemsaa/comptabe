<?php

namespace App\Helpers;

use Carbon\Carbon;

class FormatHelper
{
    /**
     * Format a date in Belgian format
     */
    public static function date($date, string $format = 'd/m/Y'): string
    {
        if (!$date) {
            return '-';
        }

        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->format($format);
    }

    /**
     * Format a datetime in Belgian format
     */
    public static function datetime($date, string $format = 'd/m/Y H:i'): string
    {
        if (!$date) {
            return '-';
        }

        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date->format($format);
    }

    /**
     * Format an amount as currency (EUR, TND, etc.)
     */
    public static function currency($amount, ?string $currency = null, ?int $decimals = null): string
    {
        if ($amount === null) {
            return '-';
        }

        // If currency not specified, get it from current company
        if ($currency === null) {
            try {
                $company = \App\Models\Company::current();
                $currency = $company->country_code === 'TN' ? 'TND' : 'EUR';
            } catch (\Exception $e) {
                $currency = 'EUR';
            }
        }

        // If decimals not specified, use country-specific default
        if ($decimals === null) {
            $decimals = match($currency) {
                'TND' => 3,  // Tunisian Dinar uses millimes (3 decimals)
                'BHD', 'KWD', 'OMR' => 3,  // Other currencies with 3 decimals
                'JPY', 'KRW' => 0,  // Currencies without decimals
                default => 2,  // Most currencies use 2 decimals
            };
        }

        $formatted = number_format((float)$amount, $decimals, ',', ' ');

        return match($currency) {
            'EUR' => $formatted . ' €',
            'TND' => $formatted . ' TND',
            'USD' => '$ ' . $formatted,
            'GBP' => '£ ' . $formatted,
            default => $formatted . ' ' . $currency,
        };
    }

    /**
     * Format a number
     */
    public static function number($number, int $decimals = 2): string
    {
        if ($number === null) {
            return '-';
        }

        return number_format((float)$number, $decimals, ',', ' ');
    }

    /**
     * Format a percentage
     */
    public static function percentage($value, int $decimals = 2): string
    {
        if ($value === null) {
            return '-';
        }

        return number_format((float)$value, $decimals, ',', ' ') . ' %';
    }

    /**
     * Format a Belgian VAT number
     */
    public static function vatNumber(?string $vatNumber): string
    {
        if (!$vatNumber) {
            return '-';
        }

        // Remove all non-alphanumeric characters
        $clean = preg_replace('/[^A-Z0-9]/i', '', strtoupper($vatNumber));

        // Belgian format: BE 0XXX.XXX.XXX
        if (str_starts_with($clean, 'BE') && strlen($clean) === 12) {
            $number = substr($clean, 2);
            return 'BE ' . substr($number, 0, 4) . '.' . substr($number, 4, 3) . '.' . substr($number, 7, 3);
        }

        return $vatNumber;
    }

    /**
     * Format an IBAN
     */
    public static function iban(?string $iban): string
    {
        if (!$iban) {
            return '-';
        }

        // Remove spaces and format in groups of 4
        $clean = preg_replace('/\s/', '', strtoupper($iban));
        return wordwrap($clean, 4, ' ', true);
    }

    /**
     * Format a Belgian structured communication
     */
    public static function structuredCommunication(?string $communication): string
    {
        if (!$communication) {
            return '-';
        }

        // Clean and format: +++XXX/XXXX/XXXXX+++
        $clean = preg_replace('/[^\d]/', '', $communication);

        if (strlen($clean) === 12) {
            return '+++' . substr($clean, 0, 3) . '/' . substr($clean, 3, 4) . '/' . substr($clean, 7, 5) . '+++';
        }

        return $communication;
    }

    /**
     * Format a phone number
     */
    public static function phone(?string $phone): string
    {
        if (!$phone) {
            return '-';
        }

        // Just return as-is for now, could add Belgian formatting later
        return $phone;
    }

    /**
     * Format file size in human readable format
     */
    public static function fileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
