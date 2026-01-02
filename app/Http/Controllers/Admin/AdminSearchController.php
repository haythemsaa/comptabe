<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;

class AdminSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = [];

        // Search Companies
        $companies = Company::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('vat_number', 'like', "%{$query}%")
            ->take(5)
            ->get();

        foreach ($companies as $company) {
            $results[] = [
                'id' => 'company_' . $company->id,
                'type' => 'company',
                'name' => $company->name,
                'subtitle' => $company->vat_number ?? $company->email ?? '',
                'initials' => strtoupper(substr($company->name, 0, 2)),
                'url' => route('admin.companies.show', $company),
            ];
        }

        // Search Users
        $users = User::where('email', 'like', "%{$query}%")
            ->orWhere('first_name', 'like', "%{$query}%")
            ->orWhere('last_name', 'like', "%{$query}%")
            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$query}%"])
            ->take(5)
            ->get();

        foreach ($users as $user) {
            $results[] = [
                'id' => 'user_' . $user->id,
                'type' => 'user',
                'name' => $user->full_name,
                'subtitle' => $user->email,
                'initials' => $user->initials,
                'url' => route('admin.users.show', $user),
            ];
        }

        return response()->json($results);
    }

    /**
     * API endpoint for searchable select component
     */
    public function companies(Request $request)
    {
        $query = Company::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('vat_number', 'like', "%{$search}%");
            });
        }

        $companies = $query->orderBy('name')
            ->paginate(20);

        // Transform for searchable select
        $companies->getCollection()->transform(function ($company) {
            return [
                'id' => $company->id,
                'name' => $company->name,
                'subtitle' => $company->vat_number ?? $company->email,
                'initials' => strtoupper(substr($company->name, 0, 2)),
            ];
        });

        return response()->json($companies);
    }

    /**
     * API endpoint for searchable select component
     */
    public function users(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('company_id')) {
            $query->whereHas('companies', fn($q) => $q->where('companies.id', $request->company_id));
        }

        $users = $query->orderBy('first_name')
            ->paginate(20);

        // Transform for searchable select
        $users->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->full_name,
                'subtitle' => $user->email,
                'initials' => $user->initials,
            ];
        });

        return response()->json($users);
    }

    /**
     * API endpoint for clients (contacts) search
     */
    public function clients(Request $request)
    {
        if (!class_exists(\App\Models\Contact::class)) {
            return response()->json(['data' => []]);
        }

        $query = \App\Models\Contact::query();

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('vat_number', 'like', "%{$search}%");
            });
        }

        $clients = $query->orderBy('name')
            ->paginate(20);

        // Transform for searchable select
        $clients->getCollection()->transform(function ($client) {
            return [
                'id' => $client->id,
                'name' => $client->name,
                'subtitle' => $client->vat_number ?? $client->email ?? '',
                'initials' => strtoupper(substr($client->name, 0, 2)),
            ];
        });

        return response()->json($clients);
    }
}
