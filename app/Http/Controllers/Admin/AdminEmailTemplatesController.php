<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class AdminEmailTemplatesController extends Controller
{
    /**
     * Display email templates
     */
    public function index(Request $request)
    {
        $query = EmailTemplate::with('lastModifiedBy');

        // Filter by category
        if ($request->filled('category')) {
            $query->category($request->category);
        }

        // Filter by type
        if ($request->input('type') === 'system') {
            $query->system();
        } elseif ($request->input('type') === 'custom') {
            $query->custom();
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } else {
                $query->where('is_active', false);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('display_name', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $templates = $query->orderBy('category')->orderBy('display_name')->paginate(20)->withQueryString();

        // Get categories for filter
        $categories = EmailTemplate::distinct()->pluck('category')->filter();

        // Statistics
        $stats = [
            'total' => EmailTemplate::count(),
            'system' => EmailTemplate::system()->count(),
            'custom' => EmailTemplate::custom()->count(),
            'active' => EmailTemplate::active()->count(),
        ];

        return view('admin.email-templates.index', compact('templates', 'categories', 'stats'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $categories = EmailTemplate::distinct()->pluck('category')->filter();

        return view('admin.email-templates.create', compact('categories'));
    }

    /**
     * Store new template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:email_templates,name|alpha_dash',
            'display_name' => 'required|string|max:255',
            'subject' => 'required|string|max:500',
            'body_html' => 'required|string',
            'body_text' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'available_variables' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['last_modified_by'] = auth()->id();
        $validated['is_system'] = false;

        $template = EmailTemplate::create($validated);

        AuditLog::log('email_template', "Template email créé: {$template->display_name}", null, [], $validated);

        return redirect()->route('admin.email-templates.show', $template)
            ->with('success', 'Template email créé avec succès.');
    }

    /**
     * Show template details
     */
    public function show(EmailTemplate $emailTemplate)
    {
        $emailTemplate->load('lastModifiedBy');

        // Get preview
        $preview = $emailTemplate->getPreview();

        return view('admin.email-templates.show', compact('emailTemplate', 'preview'));
    }

    /**
     * Show edit form
     */
    public function edit(EmailTemplate $emailTemplate)
    {
        $categories = EmailTemplate::distinct()->pluck('category')->filter();

        return view('admin.email-templates.edit', compact('emailTemplate', 'categories'));
    }

    /**
     * Update template
     */
    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('email_templates')->ignore($emailTemplate->id)],
            'display_name' => 'required|string|max:255',
            'subject' => 'required|string|max:500',
            'body_html' => 'required|string',
            'body_text' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'available_variables' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['last_modified_by'] = auth()->id();

        $emailTemplate->update($validated);

        AuditLog::log('email_template', "Template email modifié: {$emailTemplate->display_name}", null, [], $validated);

        return redirect()->route('admin.email-templates.show', $emailTemplate)
            ->with('success', 'Template email mis à jour avec succès.');
    }

    /**
     * Delete template
     */
    public function destroy(EmailTemplate $emailTemplate)
    {
        if ($emailTemplate->is_system) {
            return back()->with('error', 'Les templates système ne peuvent pas être supprimés.');
        }

        $name = $emailTemplate->display_name;
        $emailTemplate->delete();

        AuditLog::log('email_template', "Template email supprimé: {$name}");

        return redirect()->route('admin.email-templates.index')
            ->with('success', 'Template email supprimé.');
    }

    /**
     * Toggle active status
     */
    public function toggleActive(EmailTemplate $emailTemplate)
    {
        $emailTemplate->update([
            'is_active' => !$emailTemplate->is_active,
            'last_modified_by' => auth()->id(),
        ]);

        $status = $emailTemplate->is_active ? 'activé' : 'désactivé';

        return back()->with('success', "Template {$status}.");
    }

    /**
     * Duplicate template
     */
    public function duplicate(EmailTemplate $emailTemplate)
    {
        $new = $emailTemplate->replicate();
        $new->name = $emailTemplate->name . '_copy_' . time();
        $new->display_name = $emailTemplate->display_name . ' (Copie)';
        $new->is_system = false;
        $new->last_modified_by = auth()->id();
        $new->save();

        return redirect()->route('admin.email-templates.edit', $new)
            ->with('success', 'Template dupliqué. Vous pouvez maintenant le modifier.');
    }

    /**
     * Preview template
     */
    public function preview(EmailTemplate $emailTemplate)
    {
        $preview = $emailTemplate->getPreview();

        return response()->json([
            'subject' => $preview['subject'],
            'body_html' => $preview['body_html'],
            'body_text' => $preview['body_text'],
        ]);
    }

    /**
     * Send test email
     */
    public function sendTest(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $preview = $emailTemplate->getPreview();

            Mail::send([], [], function ($message) use ($validated, $preview) {
                $message->to($validated['email'])
                    ->subject('[TEST] ' . $preview['subject'])
                    ->html($preview['body_html']);
            });

            return back()->with('success', 'Email de test envoyé à ' . $validated['email']);
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'envoi: ' . $e->getMessage());
        }
    }

    /**
     * Seed default templates
     */
    public function seedDefaults()
    {
        $defaults = [
            [
                'name' => 'welcome_email',
                'display_name' => 'Email de Bienvenue',
                'subject' => 'Bienvenue sur {{ app_name }} !',
                'body_html' => '<h1>Bienvenue {{ user_name }} !</h1><p>Nous sommes ravis de vous compter parmi nous.</p><p>Commencez dès maintenant à utiliser {{ app_name }} pour gérer votre comptabilité.</p><p><a href="{{ app_url }}">Se connecter</a></p>',
                'category' => 'system',
                'description' => 'Email envoyé lors de la création d\'un nouveau compte',
                'available_variables' => ['user_name', 'user_email', 'app_name', 'app_url'],
                'is_system' => true,
            ],
            [
                'name' => 'invoice_reminder',
                'display_name' => 'Rappel Facture Impayée',
                'subject' => 'Rappel: Facture {{ invoice_number }} à payer',
                'body_html' => '<h1>Rappel de paiement</h1><p>Bonjour {{ user_name }},</p><p>Nous vous rappelons que la facture <strong>{{ invoice_number }}</strong> d\'un montant de <strong>{{ invoice_amount }}</strong> est échue depuis le {{ invoice_due_date }}.</p><p><a href="{{ payment_link }}">Payer maintenant</a></p>',
                'category' => 'invoicing',
                'description' => 'Email de rappel pour les factures impayées',
                'available_variables' => ['user_name', 'invoice_number', 'invoice_amount', 'invoice_due_date', 'payment_link', 'company_name'],
                'is_system' => true,
            ],
            [
                'name' => 'invoice_paid',
                'display_name' => 'Confirmation Paiement Facture',
                'subject' => 'Paiement reçu pour {{ invoice_number }}',
                'body_html' => '<h1>Paiement confirmé</h1><p>Bonjour {{ user_name }},</p><p>Nous avons bien reçu votre paiement de <strong>{{ invoice_amount }}</strong> pour la facture {{ invoice_number }}.</p><p>Merci pour votre confiance !</p>',
                'category' => 'invoicing',
                'description' => 'Email de confirmation de paiement',
                'available_variables' => ['user_name', 'invoice_number', 'invoice_amount', 'company_name'],
                'is_system' => true,
            ],
        ];

        $count = 0;
        foreach ($defaults as $default) {
            if (!EmailTemplate::where('name', $default['name'])->exists()) {
                EmailTemplate::create($default);
                $count++;
            }
        }

        return back()->with('success', "{$count} template(s) par défaut créé(s).");
    }
}
