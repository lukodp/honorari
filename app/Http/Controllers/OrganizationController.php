<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrganizationController extends Controller
{
    public function index(): View
    {
        $organizations = Organization::query()
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('organizations.index', compact('organizations'));
    }

    public function create(): View
    {
        return view('organizations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'house_number' => ['required', 'string', 'max:30'],
            'postal_code' => ['required', 'string', 'max:10'],
            'city' => ['required', 'string', 'max:255'],
            'oib' => ['required', 'digits:11', 'unique:organizations,oib'],
            'iban' => ['required', 'string', 'max:34'],
            'email' => ['required', 'email', 'max:255'],
            'responsible_person_first_name' => ['required', 'string', 'max:255'],
            'responsible_person_last_name' => ['required', 'string', 'max:255'],
        ]);

        $validated['is_active'] = false;

        Organization::create($validated);

        return redirect()
            ->route('organizations.index')
            ->with('success', 'Nova organizacija uspješno je dodana.');
    }

    public function activate(Organization $organization): RedirectResponse
    {
        DB::transaction(function () use ($organization) {
            Organization::query()->update(['is_active' => false]);

            $organization->update(['is_active' => true]);
        });

        return redirect()
            ->route('organizations.index')
            ->with('success', 'Aktivna organizacija uspješno je promijenjena.');
    }

    public function destroy(Organization $organization): RedirectResponse
    {
        $organization->delete();

        return redirect()
            ->route('organizations.index')
            ->with('success', 'Organizacija je obrisana.');
    }

    public function edit(): View
    {
        $organization = Organization::query()
            ->where('is_active', true)
            ->first() ?? Organization::first();

        return view('organization', compact('organization'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'house_number' => ['required', 'string', 'max:30'],
            'postal_code' => ['required', 'string', 'max:10'],
            'city' => ['required', 'string', 'max:255'],
            'oib' => ['required', 'digits:11'],
            'iban' => ['required', 'string', 'max:34'],
            'email' => ['required', 'email', 'max:255'],
            'responsible_person_first_name' => ['required', 'string', 'max:255'],
            'responsible_person_last_name' => ['required', 'string', 'max:255'],
        ]);

        $organization = Organization::query()
            ->where('is_active', true)
            ->first() ?? Organization::first();

        if ($organization) {
            $organization->update($validated);
        } else {
            Organization::create($validated);
        }

        return redirect()
            ->route('organization.edit')
            ->with('success', 'Podaci organizacije uspješno su spremljeni.');
    }
}
