<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollDeclaration extends Model
{
    use HasFactory, HasUuid, HasTenant, SoftDeletes;

    protected $fillable = [
        'company_id',
        'type',
        'period',
        'year',
        'quarter',
        'declaration_number',
        'status',
        'submitted_at',
        'submission_reference',
        'submission_channel',
        'response_message',
        'response_data',
        'xml_file_path',
        'pdf_file_path',
        'declaration_data',
        'employees_count',
        'total_gross_salary',
        'total_employee_contributions',
        'total_employer_contributions',
        'metadata',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'response_data' => 'array',
        'declaration_data' => 'array',
        'employees_count' => 'integer',
        'total_gross_salary' => 'decimal:2',
        'total_employee_contributions' => 'decimal:2',
        'total_employer_contributions' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Relationships
     */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scopes
     */

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeForPeriod($query, int $year, ?int $quarter = null)
    {
        $query->where('year', $year);

        if ($quarter) {
            $query->where('quarter', $quarter);
        }

        return $query;
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Accessors
     */

    public function getPeriodNameAttribute(): string
    {
        if ($this->quarter) {
            return "T{$this->quarter} {$this->year}";
        }

        if ($this->period) {
            [$year, $month] = explode('-', $this->period);
            $monthNames = [
                '01' => 'Janvier', '02' => 'Février', '03' => 'Mars', '04' => 'Avril',
                '05' => 'Mai', '06' => 'Juin', '07' => 'Juillet', '08' => 'Août',
                '09' => 'Septembre', '10' => 'Octobre', '11' => 'Novembre', '12' => 'Décembre',
            ];

            return ($monthNames[$month] ?? $month) . ' ' . $year;
        }

        return (string) $this->year;
    }

    public function getTypeNameAttribute(): string
    {
        $types = [
            'dimona' => 'DIMONA (Déclaration immédiate)',
            'dmfa' => 'DmfA (Déclaration Multi-Fonctionnelle)',
            'tax_281_10' => 'Fiche 281.10 (Salaires)',
            'tax_281_20' => 'Fiche 281.20 (Commissions)',
            'annual_account' => 'Compte individuel',
        ];

        return $types[$this->type] ?? $this->type;
    }

    /**
     * Methods
     */

    /**
     * Mark declaration as ready for submission.
     */
    public function markAsReady(): bool
    {
        if ($this->status !== 'draft') {
            return false;
        }

        $this->update(['status' => 'ready']);

        return true;
    }

    /**
     * Submit the declaration.
     */
    public function submit(string $channel = 'web'): bool
    {
        if (!in_array($this->status, ['ready', 'rejected'])) {
            return false;
        }

        $this->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'submission_channel' => $channel,
        ]);

        return true;
    }

    /**
     * Mark declaration as accepted.
     */
    public function markAsAccepted(string $reference, ?string $message = null): void
    {
        $this->update([
            'status' => 'accepted',
            'submission_reference' => $reference,
            'response_message' => $message,
        ]);
    }

    /**
     * Mark declaration as rejected.
     */
    public function markAsRejected(string $message): void
    {
        $this->update([
            'status' => 'rejected',
            'response_message' => $message,
        ]);
    }

    /**
     * Generate declaration number.
     */
    public static function generateDeclarationNumber(Company $company, string $type, int $year, ?int $quarter = null): string
    {
        $typeCode = match ($type) {
            'dimona' => 'DIM',
            'dmfa' => 'DMF',
            'tax_281_10' => '281',
            'tax_281_20' => '282',
            'annual_account' => 'ANN',
            default => 'DEC',
        };

        if ($quarter) {
            $period = "Q{$quarter}";
        } else {
            $period = $year;
        }

        $count = self::where('company_id', $company->id)
            ->where('type', $type)
            ->where('year', $year)
            ->when($quarter, fn($q) => $q->where('quarter', $quarter))
            ->count() + 1;

        return sprintf('%s-%s-%04d-%03d', $typeCode, $period, $year, $count);
    }

    /**
     * Generate XML for submission.
     */
    public function generateXML(): string
    {
        // TODO: Implement XML generation based on type
        // Each declaration type has its own XML schema

        switch ($this->type) {
            case 'dimona':
                return $this->generateDIMONAXML();
            case 'dmfa':
                return $this->generateDmfAXML();
            case 'tax_281_10':
                return $this->generate281XML();
            default:
                throw new \Exception("XML generation not implemented for type: {$this->type}");
        }
    }

    /**
     * Generate DIMONA XML.
     */
    protected function generateDIMONAXML(): string
    {
        // DIMONA XML structure for new employee declaration
        // See: https://www.socialsecurity.be/site_fr/employer/applics/dimona/index.htm

        return '<?xml version="1.0" encoding="UTF-8"?>
<DIMONA xmlns="http://www.socialsecurity.be/dimona">
    <!-- DIMONA XML content -->
</DIMONA>';
    }

    /**
     * Generate DmfA XML.
     */
    protected function generateDmfAXML(): string
    {
        // DmfA XML structure for quarterly social security declaration
        // See: https://www.socialsecurity.be/site_fr/employer/applics/dmfa/index.htm

        return '<?xml version="1.0" encoding="UTF-8"?>
<DmfA xmlns="http://www.socialsecurity.be/dmfa">
    <!-- DmfA XML content -->
</DmfA>';
    }

    /**
     * Generate 281 XML.
     */
    protected function generate281XML(): string
    {
        // Fiche 281.10 XML structure for annual tax declaration
        // See: https://finances.belgium.be/fr/E-services/Belcotax-on-web

        return '<?xml version="1.0" encoding="UTF-8"?>
<Fiche281 xmlns="http://www.minfin.fgov.be">
    <!-- 281.10 XML content -->
</Fiche281>';
    }

    /**
     * Check if declaration can be edited.
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'rejected']);
    }

    /**
     * Check if declaration can be submitted.
     */
    public function canBeSubmitted(): bool
    {
        return in_array($this->status, ['ready', 'rejected']);
    }

    /**
     * Check if declaration can be deleted.
     */
    public function canBeDeleted(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Get deadline for submission based on type.
     */
    public function getSubmissionDeadline(): ?\Carbon\Carbon
    {
        switch ($this->type) {
            case 'dimona':
                // DIMONA must be submitted before employee starts work
                return null; // Immediate

            case 'dmfa':
                // DmfA quarterly declaration due on last day of month following quarter
                if ($this->quarter) {
                    $lastMonthOfQuarter = $this->quarter * 3;
                    return \Carbon\Carbon::create($this->year, $lastMonthOfQuarter, 1)
                        ->endOfMonth()
                        ->addMonth();
                }
                return null;

            case 'tax_281_10':
            case 'tax_281_20':
                // Fiche 281 due by March 1st of following year
                return \Carbon\Carbon::create($this->year + 1, 3, 1);

            case 'annual_account':
                // Compte individuel due by March 31st of following year
                return \Carbon\Carbon::create($this->year + 1, 3, 31);

            default:
                return null;
        }
    }

    /**
     * Check if declaration is overdue.
     */
    public function isOverdue(): bool
    {
        if ($this->status === 'accepted') {
            return false;
        }

        $deadline = $this->getSubmissionDeadline();

        if (!$deadline) {
            return false;
        }

        return now()->isAfter($deadline);
    }
}
