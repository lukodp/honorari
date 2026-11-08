<?php

namespace App\Http\Controllers;

use App\Models\LocalGovernment;
use App\Models\Referee;
use Illuminate\Http\Request;

class RefereeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $referees = Referee::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(15)
            ->withQueryString();

        return view('referees.index', compact('referees', 'search'));
    }

    public function create()
    {
    $localGovernments = LocalGovernment::query()
        ->orderBy('name')
        ->get();

    return view('referees.create', compact('localGovernments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'oib' => ['required', 'digits:11', 'unique:referees,oib'],
            'address' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:10'],
            'local_government_id' => [
                'required',
                'exists:local_governments,id',
            ],
            'iban' => ['required', 'string', 'max:34'],
        ]);

        $localGovernment = LocalGovernment::findOrFail(
            $validated['local_government_id']
        );

        $validated['city'] = $localGovernment->name;

        Referee::create($validated);

        return redirect()
            ->route('primatelji.index')
            ->with('success', 'Sudac je uspješno dodan.');
    }

    public function edit(Referee $referee)
    {
    $localGovernments = LocalGovernment::query()
        ->orderBy('name')
        ->get();

    return view(
        'referees.edit',
        compact('referee', 'localGovernments')
    );
    }

    public function update(Request $request, Referee $referee)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'oib' => [
                'required',
                'digits:11',
                'unique:referees,oib,' . $referee->id,
            ],
            'address' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:10'],
            'local_government_id' => [
                'required',
                'exists:local_governments,id',
            ],
            'iban' => ['required', 'string', 'max:34'],
            'is_active' => ['required', 'boolean'],
        ]);

        $localGovernment = LocalGovernment::findOrFail(
            $validated['local_government_id']
        );

        $validated['city'] = $localGovernment->name;

        $referee->update($validated);

        return redirect()
            ->route('primatelji.index')
            ->with('success', 'Podaci suca uspješno ažurirani.');
    }
    public function destroy(Referee $referee)
    {
        $referee->delete();

        return redirect()
            ->route('primatelji.index')
            ->with('success', 'Sudac je obrisan.');
    }
}
