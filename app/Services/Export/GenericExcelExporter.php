<?php

namespace App\Services\Export;

/**
 * Exporter Excel générique
 * Utilise le CSV avec conversion Excel via maatwebsite/excel
 */
class GenericExcelExporter extends GenericCsvExporter
{
    // Pour l'instant, utilise le même format CSV
    // Peut être étendu pour utiliser PhpSpreadsheet si nécessaire
}
