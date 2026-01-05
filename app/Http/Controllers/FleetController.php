<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleAtn;
use App\Models\VehicleContract;
use App\Models\VehicleExpense;
use App\Models\VehicleOdometerReading;
use App\Models\VehicleReservation;
use App\Models\VehicleReminder;
use App\Models\User;
use Illuminate\Http\Request;

class FleetController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::where('company_id', auth()->user()->current_company_id)
            ->with(['assignedUser', 'activeContract']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('license_plate', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        $vehicles = $query->orderBy('brand')->orderBy('model')->paginate(20);

        $stats = [
            'total' => Vehicle::where('company_id', auth()->user()->current_company_id)->count(),
            'active' => Vehicle::where('company_id', auth()->user()->current_company_id)->where('status', 'active')->count(),
            'total_value' => Vehicle::where('company_id', auth()->user()->current_company_id)
                ->where('status', 'active')
                ->sum(\DB::raw('catalog_value + options_value')),
            'pending_reminders' => VehicleReminder::whereHas('vehicle', fn($q) =>
                $q->where('company_id', auth()->user()->current_company_id)
            )->dueSoon(30)->count(),
        ];

        return view('fleet.index', compact('vehicles', 'stats'));
    }

    public function create()
    {
        $users = User::whereHas('companies', fn($q) =>
            $q->where('companies.id', auth()->user()->current_company_id)
        )->orderBy('name')->get();

        return view('fleet.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'assigned_user_id' => 'nullable|exists:users,id',
            'license_plate' => 'nullable|string|max:20',
            'vin' => 'nullable|string|max:50',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'type' => 'required|in:car,van,truck,motorcycle,electric_bike,other',
            'fuel_type' => 'required|in:petrol,diesel,hybrid,electric,lpg,cng,hydrogen',
            'ownership' => 'required|in:owned,leased,rented,employee_owned',
            'co2_emission' => 'nullable|integer|min:0|max:500',
            'emission_standard' => 'nullable|in:euro1,euro2,euro3,euro4,euro5,euro6,euro6d',
            'fiscal_horsepower' => 'nullable|integer|min:1|max:100',
            'engine_power_kw' => 'nullable|integer|min:1|max:1000',
            'battery_capacity_kwh' => 'nullable|integer|min:1|max:200',
            'catalog_value' => 'nullable|numeric|min:0',
            'options_value' => 'nullable|numeric|min:0',
            'first_registration_date' => 'nullable|date',
            'acquisition_date' => 'nullable|date',
            'odometer_start' => 'integer|min:0',
            'insurance_company' => 'nullable|string|max:255',
            'insurance_policy_number' => 'nullable|string|max:100',
            'insurance_expiry_date' => 'nullable|date',
            'technical_inspection_date' => 'nullable|date',
        ]);

        $validated['company_id'] = auth()->user()->current_company_id;
        $validated['status'] = 'active';
        $validated['odometer_current'] = $validated['odometer_start'] ?? 0;

        $vehicle = Vehicle::create($validated);

        // Create reminders
        if ($validated['insurance_expiry_date'] ?? null) {
            VehicleReminder::create([
                'vehicle_id' => $vehicle->id,
                'type' => 'insurance',
                'due_date' => $validated['insurance_expiry_date'],
                'reminder_days_before' => 30,
                'is_recurring' => true,
                'recurrence_months' => 12,
            ]);
        }

        if ($validated['technical_inspection_date'] ?? null) {
            VehicleReminder::create([
                'vehicle_id' => $vehicle->id,
                'type' => 'technical_inspection',
                'due_date' => $validated['technical_inspection_date'],
                'reminder_days_before' => 30,
                'is_recurring' => true,
                'recurrence_months' => 12,
            ]);
        }

        return redirect()
            ->route('fleet.show', $vehicle)
            ->with('success', 'Véhicule ajouté avec succès.');
    }

    public function show(Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);

        $vehicle->load([
            'assignedUser',
            'contracts.partner',
            'expenses' => fn($q) => $q->latest('expense_date')->limit(10),
            'odometerReadings' => fn($q) => $q->latest('reading_date')->limit(10),
            'pendingReminders',
            'asset',
        ]);

        // Calculate ATN for current month
        $atn = null;
        if ($vehicle->assigned_user_id && $vehicle->catalog_value) {
            $atn = $vehicle->calculateAtn();
        }

        // Expense stats
        $expenseStats = VehicleExpense::where('vehicle_id', $vehicle->id)
            ->selectRaw('type, SUM(amount) as total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        return view('fleet.show', compact('vehicle', 'atn', 'expenseStats'));
    }

    public function edit(Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);

        $users = User::whereHas('companies', fn($q) =>
            $q->where('companies.id', auth()->user()->current_company_id)
        )->orderBy('name')->get();

        return view('fleet.edit', compact('vehicle', 'users'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);

        $validated = $request->validate([
            'assigned_user_id' => 'nullable|exists:users,id',
            'license_plate' => 'nullable|string|max:20',
            'vin' => 'nullable|string|max:50',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'type' => 'required|in:car,van,truck,motorcycle,electric_bike,other',
            'fuel_type' => 'required|in:petrol,diesel,hybrid,electric,lpg,cng,hydrogen',
            'ownership' => 'required|in:owned,leased,rented,employee_owned',
            'co2_emission' => 'nullable|integer|min:0|max:500',
            'emission_standard' => 'nullable|in:euro1,euro2,euro3,euro4,euro5,euro6,euro6d',
            'catalog_value' => 'nullable|numeric|min:0',
            'options_value' => 'nullable|numeric|min:0',
            'status' => 'in:active,maintenance,disposed,sold',
        ]);

        $vehicle->update($validated);

        return redirect()
            ->route('fleet.show', $vehicle)
            ->with('success', 'Véhicule mis à jour.');
    }

    public function destroy(Vehicle $vehicle)
    {
        $this->authorize('delete', $vehicle);

        $vehicle->delete();

        return redirect()
            ->route('fleet.index')
            ->with('success', 'Véhicule supprimé.');
    }

    // Expenses
    public function expenses(Request $request, Vehicle $vehicle = null)
    {
        $query = VehicleExpense::where('company_id', auth()->user()->current_company_id)
            ->with(['vehicle', 'user']);

        if ($vehicle) {
            $query->where('vehicle_id', $vehicle->id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }

        $expenses = $query->orderByDesc('expense_date')->paginate(20);

        $vehicles = Vehicle::where('company_id', auth()->user()->current_company_id)
            ->where('status', 'active')
            ->orderBy('brand')
            ->get();

        return view('fleet.expenses.index', compact('expenses', 'vehicles', 'vehicle'));
    }

    public function storeExpense(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'type' => 'required|in:fuel,maintenance,repair,insurance,tax,parking,toll,washing,tyre,fine,other',
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'vat_amount' => 'numeric|min:0',
            'quantity' => 'nullable|numeric|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'odometer' => 'nullable|integer|min:0',
            'supplier' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_private_use' => 'boolean',
            'private_use_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $validated['company_id'] = auth()->user()->current_company_id;
        $validated['user_id'] = auth()->id();

        $expense = VehicleExpense::create($validated);

        // Update odometer if provided
        if ($validated['odometer'] ?? null) {
            VehicleOdometerReading::create([
                'vehicle_id' => $validated['vehicle_id'],
                'user_id' => auth()->id(),
                'reading_date' => $validated['expense_date'],
                'odometer_value' => $validated['odometer'],
                'notes' => 'Via dépense: ' . $expense->type_label,
            ]);
        }

        return back()->with('success', 'Dépense enregistrée.');
    }

    // ATN Calculation
    public function atnReport(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month');

        $vehicles = Vehicle::where('company_id', auth()->user()->current_company_id)
            ->whereNotNull('assigned_user_id')
            ->whereNotNull('catalog_value')
            ->where('status', 'active')
            ->with('assignedUser')
            ->get();

        $atnRecords = [];
        foreach ($vehicles as $vehicle) {
            if ($month) {
                $atn = VehicleAtn::calculateForVehicle($vehicle, $year, $month);
                $atnRecords[] = $atn;
            } else {
                // Full year
                for ($m = 1; $m <= 12; $m++) {
                    $atn = VehicleAtn::calculateForVehicle($vehicle, $year, $m);
                    $atnRecords[] = $atn;
                }
            }
        }

        $summary = [
            'total_atn' => collect($atnRecords)->sum('atn_amount'),
            'total_solidarity' => collect($atnRecords)->sum('employer_solidarity_contribution'),
            'vehicles_count' => $vehicles->count(),
        ];

        return view('fleet.atn-report', compact('vehicles', 'atnRecords', 'summary', 'year', 'month'));
    }

    // Reservations
    public function reservations(Request $request)
    {
        $query = VehicleReservation::whereHas('vehicle', fn($q) =>
            $q->where('company_id', auth()->user()->current_company_id)
        )->with(['vehicle', 'user']);

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reservations = $query->orderByDesc('start_datetime')->paginate(20);

        $vehicles = Vehicle::where('company_id', auth()->user()->current_company_id)
            ->where('status', 'active')
            ->orderBy('brand')
            ->get();

        return view('fleet.reservations.index', compact('reservations', 'vehicles'));
    }

    public function storeReservation(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'start_datetime' => 'required|date|after:now',
            'end_datetime' => 'required|date|after:start_datetime',
            'purpose' => 'nullable|string|max:255',
            'destination' => 'nullable|string|max:255',
            'expected_km' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        // Check for overlaps
        $reservation = new VehicleReservation($validated);
        if ($reservation->overlaps($validated['start_datetime'], $validated['end_datetime'])) {
            return back()->with('error', 'Ce véhicule est déjà réservé pour cette période.')->withInput();
        }

        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending';

        VehicleReservation::create($validated);

        return back()->with('success', 'Réservation créée.');
    }

    public function approveReservation(VehicleReservation $reservation)
    {
        $this->authorize('update', $reservation->vehicle);

        $reservation->update(['status' => 'approved']);

        return back()->with('success', 'Réservation approuvée.');
    }

    public function rejectReservation(VehicleReservation $reservation)
    {
        $this->authorize('update', $reservation->vehicle);

        $reservation->update(['status' => 'rejected']);

        return back()->with('success', 'Réservation refusée.');
    }

    // Reminders
    public function reminders()
    {
        $reminders = VehicleReminder::whereHas('vehicle', fn($q) =>
            $q->where('company_id', auth()->user()->current_company_id)
        )
            ->with('vehicle')
            ->whereIn('status', ['pending', 'notified', 'overdue'])
            ->orderBy('due_date')
            ->paginate(20);

        return view('fleet.reminders.index', compact('reminders'));
    }

    public function completeReminder(VehicleReminder $reminder)
    {
        $this->authorize('update', $reminder->vehicle);

        $reminder->complete();

        return back()->with('success', 'Rappel marqué comme terminé.');
    }
}
