<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplate extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'subject',
        'body_html',
        'body_text',
        'available_variables',
        'category',
        'description',
        'is_active',
        'is_system',
        'last_modified_by',
    ];

    protected $casts = [
        'available_variables' => 'array',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    /**
     * Get the user who last modified this template
     */
    public function lastModifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_modified_by');
    }

    /**
     * Render the template with given variables
     */
    public function render(array $variables = []): array
    {
        $subject = $this->renderString($this->subject, $variables);
        $bodyHtml = $this->renderString($this->body_html, $variables);
        $bodyText = $this->body_text ? $this->renderString($this->body_text, $variables) : null;

        return [
            'subject' => $subject,
            'body_html' => $bodyHtml,
            'body_text' => $bodyText,
        ];
    }

    /**
     * Render a string by replacing variables
     */
    protected function renderString(string $template, array $variables): string
    {
        $rendered = $template;

        foreach ($variables as $key => $value) {
            // Support both {{ variable }} and {{ $variable }} syntax
            $rendered = str_replace([
                '{{ ' . $key . ' }}',
                '{{' . $key . '}}',
                '{{ $' . $key . ' }}',
                '{{$' . $key . '}}',
            ], $value, $rendered);
        }

        return $rendered;
    }

    /**
     * Get preview with sample data
     */
    public function getPreview(): array
    {
        $sampleData = $this->getSampleData();
        return $this->render($sampleData);
    }

    /**
     * Get sample data for preview
     */
    protected function getSampleData(): array
    {
        $samples = [
            'company_name' => 'Acme Corporation',
            'user_name' => 'Jean Dupont',
            'user_email' => 'jean.dupont@example.com',
            'invoice_number' => 'INV-2025-001',
            'invoice_amount' => '1,250.00 €',
            'invoice_due_date' => '31/01/2025',
            'payment_link' => 'https://example.com/pay/inv-001',
            'support_email' => 'support@comptabe.com',
            'app_name' => 'ComptaBE',
            'app_url' => url('/'),
            'current_year' => date('Y'),
        ];

        // Merge with available variables if defined
        if ($this->available_variables) {
            foreach ($this->available_variables as $var) {
                if (!isset($samples[$var])) {
                    $samples[$var] = '[' . $var . ']';
                }
            }
        }

        return $samples;
    }

    /**
     * Scope for active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for system templates
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope for custom templates
     */
    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * Scope by category
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
