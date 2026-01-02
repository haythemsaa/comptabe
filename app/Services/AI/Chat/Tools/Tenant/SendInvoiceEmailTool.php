<?php

namespace App\Services\AI\Chat\Tools\Tenant;

use App\Models\Invoice;
use App\Services\AI\Chat\Tools\AbstractTool;
use App\Services\AI\Chat\Tools\ToolContext;
use Illuminate\Support\Facades\Mail;

class SendInvoiceEmailTool extends AbstractTool
{
    public function getName(): string
    {
        return 'send_invoice_email';
    }

    public function getDescription(): string
    {
        return 'Sends an invoice to the customer by email with PDF attachment. Use this when the user wants to send, email, or transmit an invoice to their customer.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'invoice_id' => [
                    'type' => 'string',
                    'description' => 'UUID of the invoice to send',
                ],
                'invoice_number' => [
                    'type' => 'string',
                    'description' => 'Invoice number if invoice_id is unknown (e.g., F-2025-001)',
                ],
                'recipient_email' => [
                    'type' => 'string',
                    'format' => 'email',
                    'description' => 'Email address to send to (defaults to partner email)',
                ],
                'cc_emails' => [
                    'type' => 'array',
                    'items' => ['type' => 'string', 'format' => 'email'],
                    'description' => 'Additional email addresses to CC',
                ],
                'subject' => [
                    'type' => 'string',
                    'description' => 'Email subject (defaults to "Facture {invoice_number}")',
                ],
                'message' => [
                    'type' => 'string',
                    'description' => 'Personal message to include in the email body',
                ],
                'attach_pdf' => [
                    'type' => 'boolean',
                    'description' => 'Whether to attach PDF (default: true)',
                ],
            ],
            'required' => [],
        ];
    }

    public function requiresConfirmation(): bool
    {
        return true;
    }

    public function execute(array $input, ToolContext $context): array
    {
        // Validate tenant access
        $this->validateTenantAccess($context->user, $context->company);

        // Find invoice
        $invoice = $this->findInvoice($input, $context);

        if (!$invoice) {
            return [
                'error' => 'Invoice not found. Please provide a valid invoice_id or invoice_number.',
                'suggestion' => 'Use the read_invoices tool to find the invoice first.',
            ];
        }

        // Load partner
        $invoice->load('partner');

        if (!$invoice->partner) {
            return [
                'error' => "La facture {$invoice->invoice_number} n'a pas de client associé.",
            ];
        }

        // Determine recipient email
        $recipientEmail = $input['recipient_email'] ?? $invoice->partner->email;

        if (!$recipientEmail) {
            return [
                'error' => "Aucune adresse email disponible pour le client {$invoice->partner->name}.",
                'suggestion' => 'Spécifiez une adresse email avec le paramètre recipient_email.',
            ];
        }

        // Check if invoice is in draft status
        if ($invoice->status === 'draft') {
            return [
                'error' => "La facture {$invoice->invoice_number} est en brouillon. Validez-la d'abord avant de l'envoyer.",
                'suggestion' => 'Validez la facture ou changez son statut.',
            ];
        }

        // Prepare email data
        $subject = $input['subject'] ?? "Facture {$invoice->invoice_number}";
        $attachPdf = $input['attach_pdf'] ?? true;
        $ccEmails = $input['cc_emails'] ?? [];

        try {
            // Send email
            Mail::send('emails.invoice', [
                'invoice' => $invoice,
                'company' => $context->company,
                'customMessage' => $input['message'] ?? null,
            ], function ($message) use ($recipientEmail, $ccEmails, $subject, $invoice, $attachPdf) {
                $message->to($recipientEmail)
                    ->subject($subject);

                if (!empty($ccEmails)) {
                    $message->cc($ccEmails);
                }

                if ($attachPdf && $invoice->pdf_path) {
                    $message->attach(storage_path('app/' . $invoice->pdf_path));
                }
            });

            // Update invoice status
            if ($invoice->status === 'validated') {
                $invoice->status = 'sent';
            }
            $invoice->save();

            return [
                'success' => true,
                'message' => "Facture {$invoice->invoice_number} envoyée avec succès à {$recipientEmail}",
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'partner' => $invoice->partner->name,
                    'status' => $invoice->status,
                    'total_incl_vat' => (float) $invoice->total_incl_vat,
                    'currency' => 'EUR',
                ],
                'email' => [
                    'to' => $recipientEmail,
                    'cc' => $ccEmails,
                    'subject' => $subject,
                    'pdf_attached' => $attachPdf,
                ],
            ];

        } catch (\Exception $e) {
            \Log::error('Failed to send invoice email', [
                'invoice_id' => $invoice->id,
                'email' => $recipientEmail,
                'error' => $e->getMessage(),
            ]);

            return [
                'error' => "Échec de l'envoi de l'email : " . $e->getMessage(),
                'suggestion' => 'Vérifiez la configuration email du serveur.',
            ];
        }
    }

    /**
     * Find invoice by ID or number.
     */
    protected function findInvoice(array $input, ToolContext $context): ?Invoice
    {
        // Try to find by ID first
        if (!empty($input['invoice_id'])) {
            return Invoice::where('id', $input['invoice_id'])
                ->where('company_id', $context->company->id)
                ->first();
        }

        // Try to find by invoice number
        if (!empty($input['invoice_number'])) {
            return Invoice::where('company_id', $context->company->id)
                ->where('invoice_number', $input['invoice_number'])
                ->first();
        }

        return null;
    }
}
