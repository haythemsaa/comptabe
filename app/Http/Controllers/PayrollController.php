<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Payslip;
use App\Models\EmploymentContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    /**
     * Display payroll dashboard
     */
    public function index()
    {
        $company = Company::current();

        $stats = [
            'total_employees' => Employee::where('company_id', $company->id)->count(),
            'active_employees' => Employee::where('company_id', $company->id)->where('status', 'active')->count(),
            'payslips_this_month' => Payslip::where('company_id', $company->id)
                ->where('year', now()->year)
                ->where('month', now()->month)
                ->count(),
            'total_payroll_cost' => Payslip::where('company_id', $company->id)
                ->where('year', now()->year)
                ->where('month', now()->month)
                ->sum('total_employer_cost'),
        ];

        return view('payroll.index', compact('stats'));
    }

    /**
     * Display list of employees
     */
    public function employees(Request $request)
    {
        $company = Company::current();

        $query = Employee::where('company_id', $company->id)
            ->with(['activeContract', 'latestPayslip']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('employee_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $employees = $query->paginate(20);

        return view('payroll.employees.index', compact('employees'));
    }

    /**
     * Show employee details
     */
    public function showEmployee(Employee $employee)
    {
        $this->authorize('view', $employee);

        $employee->load(['contracts', 'payslips' => function ($query) {
            $query->latest('year')->latest('month')->take(12);
        }]);

        return view('payroll.employees.show', compact('employee'));
    }

    /**
     * Show form to create new employee
     */
    public function createEmployee()
    {
        $company = Company::current();
        return view('payroll.employees.create', compact('company'));
    }

    /**
     * Store new employee
     */
    public function storeEmployee(Request $request)
    {
        $company = Company::current();

        // Base validation rules
        $rules = [
            'employee_number' => 'required|string|max:50|unique:employees,employee_number',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'birth_date' => 'required|date|before:today',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'hire_date' => 'required|date',
            'street' => 'nullable|string|max:255',
            'house_number' => 'nullable|string|max:20',
            'postal_code' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:100',
            'country_code' => 'nullable|string|size:2',
            'status' => 'required|in:active,on_leave,terminated',
        ];

        // Add country-specific validation rules
        if ($company->country_code === 'TN') {
            // Tunisia: CIN, CNSS number, RIB
            $rules['cin'] = 'nullable|string|max:20|unique:employees,cin';
            $rules['cnss_number'] = 'nullable|string|max:20|unique:employees,cnss_number';
            $rules['rib'] = 'nullable|string|max:20';
        } else {
            // Belgium and others: National number, IBAN
            $rules['national_number'] = 'nullable|string|max:20|unique:employees,national_number';
            $rules['iban'] = 'nullable|string|max:34';
        }

        $validated = $request->validate($rules);
        $validated['company_id'] = $company->id;

        $employee = Employee::create($validated);

        return redirect()
            ->route('payroll.employees.show', $employee)
            ->with('success', 'Employé créé avec succès.');
    }

    /**
     * Show form to edit employee
     */
    public function editEmployee(Employee $employee)
    {
        $this->authorize('update', $employee);

        $company = Company::current();
        return view('payroll.employees.edit', compact('employee', 'company'));
    }

    /**
     * Update employee
     */
    public function updateEmployee(Request $request, Employee $employee)
    {
        $this->authorize('update', $employee);

        $company = Company::current();

        // Base validation rules
        $rules = [
            'employee_number' => 'required|string|max:50|unique:employees,employee_number,' . $employee->id,
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'birth_date' => 'required|date|before:today',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'hire_date' => 'required|date',
            'termination_date' => 'nullable|date|after:hire_date',
            'street' => 'nullable|string|max:255',
            'house_number' => 'nullable|string|max:20',
            'postal_code' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:100',
            'country_code' => 'nullable|string|size:2',
            'status' => 'required|in:active,on_leave,terminated',
        ];

        // Add country-specific validation rules
        if ($company->country_code === 'TN') {
            // Tunisia: CIN, CNSS number, RIB
            $rules['cin'] = 'nullable|string|max:20|unique:employees,cin,' . $employee->id;
            $rules['cnss_number'] = 'nullable|string|max:20|unique:employees,cnss_number,' . $employee->id;
            $rules['rib'] = 'nullable|string|max:20';
        } else {
            // Belgium and others: National number, IBAN
            $rules['national_number'] = 'nullable|string|max:20|unique:employees,national_number,' . $employee->id;
            $rules['iban'] = 'nullable|string|max:34';
        }

        $validated = $request->validate($rules);

        $employee->update($validated);

        return redirect()
            ->route('payroll.employees.show', $employee)
            ->with('success', 'Employé mis à jour avec succès.');
    }

    /**
     * Delete employee
     */
    public function destroyEmployee(Employee $employee)
    {
        $this->authorize('delete', $employee);

        $employee->delete();

        return redirect()
            ->route('payroll.employees.index')
            ->with('success', 'Employé supprimé avec succès.');
    }

    /**
     * Display list of payslips
     */
    public function payslips(Request $request)
    {
        $company = Company::current();

        $query = Payslip::where('company_id', $company->id)
            ->with(['employee']);

        // Filter by year/month
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Sort
        $query->orderBy('year', 'desc')
            ->orderBy('month', 'desc');

        $payslips = $query->paginate(20);

        // Get filter options
        $employees = Employee::where('company_id', $company->id)
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'employee_number']);

        return view('payroll.payslips.index', compact('payslips', 'employees'));
    }

    /**
     * Show payslip details
     */
    public function showPayslip(Payslip $payslip)
    {
        $this->authorize('view', $payslip);

        $payslip->load(['employee', 'company', 'validator']);

        return view('payroll.payslips.show', compact('payslip'));
    }

    /**
     * Validate a payslip
     */
    public function validatePayslip(Payslip $payslip)
    {
        $this->authorize('update', $payslip);

        if ($payslip->status !== 'draft') {
            return back()->with('error', 'Seules les fiches en brouillon peuvent être validées.');
        }

        $payslip->validate(auth()->user());

        return back()->with('success', 'Fiche de paie validée avec succès.');
    }

    /**
     * Mark payslip as paid
     */
    public function markAsPaid(Payslip $payslip)
    {
        $this->authorize('update', $payslip);

        if ($payslip->status !== 'validated') {
            return back()->with('error', 'Seules les fiches validées peuvent être marquées comme payées.');
        }

        $payslip->markAsPaid();

        return back()->with('success', 'Fiche de paie marquée comme payée.');
    }

    /**
     * Download payslip PDF
     */
    public function downloadPayslipPDF(Payslip $payslip)
    {
        $this->authorize('view', $payslip);

        try {
            // Generate PDF if not already generated or regenerate if requested
            if (!$payslip->pdf_path || request()->has('regenerate')) {
                $payslip->generatePDF();
            }

            // Check if PDF file exists in storage
            if ($payslip->pdf_path && \Storage::disk('local')->exists($payslip->pdf_path)) {
                $pdfContent = \Storage::disk('local')->get($payslip->pdf_path);

                return response($pdfContent, 200)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'inline; filename="fiche-paie-' . $payslip->payslip_number . '.pdf"');
            }

            // If file doesn't exist, generate it directly
            $pdf = \PDF::loadView('pdf.payslip', [
                'payslip' => $payslip,
                'employee' => $payslip->employee,
                'company' => $payslip->company,
            ]);

            $pdf->setPaper('a4', 'portrait');

            return $pdf->stream('fiche-paie-' . $payslip->payslip_number . '.pdf');

        } catch (\Exception $e) {
            \Log::error('Payslip PDF generation failed', [
                'payslip_id' => $payslip->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Erreur lors de la génération du PDF: ' . $e->getMessage());
        }
    }
}
