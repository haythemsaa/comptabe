<?php

namespace App\Services\Export;

/**
 * Exporter pour Horus
 * Utilise le format Excel (xlsx) via le CSV générique
 */
class HorusExporter extends GenericCsvExporter
{
    // Horus peut importer du CSV/Excel
    // Utilise le même format que le générique
}
