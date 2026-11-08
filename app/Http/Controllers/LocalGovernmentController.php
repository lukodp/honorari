<?php

namespace App\Http\Controllers;

use App\Models\LocalGovernment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocalGovernmentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'regex:/^\d{2,5}$/', Rule::unique('local_governments', 'code')],
            'name' => ['required', 'string', 'max:255'],
        ], [
            'code.regex' => 'Šifra mora sadržavati od 2 do 5 znamenki.',
            'code.unique' => 'Ta šifra općine ili grada već postoji.',
        ]);

        LocalGovernment::create($validated);

        return redirect()
            ->route('porezne-stope.create')
            ->with('success', 'Općina ili grad uspješno je dodan. Sada možete unijeti pripadajuću poreznu stopu.');
    }
}
