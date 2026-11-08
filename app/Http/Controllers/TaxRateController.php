<?php

namespace App\Http\Controllers;

use App\Models\LocalGovernment;
use App\Models\TaxRate;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TaxRateController extends Controller
{
    private function calculateCoefficient(float $rate): string
    {
    $coefficient = 1 / (1 - ($rate / 100));

    return number_format($coefficient, 6, '.', '');
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $normalizedSearch = strtolower(strtr($search, [
            'Č' => 'č',
            'Ć' => 'ć',
            'Š' => 'š',
            'Đ' => 'đ',
            'Ž' => 'ž',
        ]));

        $taxRates = TaxRate::query()
            ->when($search !== '', function ($query) use ($normalizedSearch) {
                $query->whereRaw(
                    "LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(city, 'Č', 'č'), 'Ć', 'ć'), 'Š', 'š'), 'Đ', 'đ'), 'Ž', 'ž')) LIKE ?",
                    ['%' . $normalizedSearch . '%']
                );
            })
            ->orderBy('city')
            ->orderByDesc('valid_from')
            ->paginate(15)
            ->withQueryString();

        return view('tax-rates.index', compact('taxRates', 'search'));
    }

    public function create()
    {
        $localGovernments = LocalGovernment::query()
            ->orderBy('name')
            ->get();

        return view(
            'tax-rates.create',
            compact('localGovernments')
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'local_government_id' => [
                'required',
                'exists:local_governments,id',
            ],
            'iban' => [
                'required',
                'string',
                'max:34',
                'regex:/^[A-Z]{2}[0-9A-Z]{13,32}$/i',
            ],
            'rate' => [
                'required',
                'numeric',
                'in:15,17,17.5,18,19,20,20.5,21,21.3,21.4,21.5,21.6,21.8,22,22.2,22.3,22.4,23,23.6',
            ],
            'valid_from' => ['required', 'date'],
            'valid_until' => [
                'nullable',
                'date',
                'after_or_equal:valid_from',
            ],
        ]);

        $localGovernment = LocalGovernment::findOrFail(
            $validated['local_government_id']
        );

        $validated['city'] = $localGovernment->name;

        $validated['iban'] = strtoupper(
            preg_replace('/\s+/', '', $validated['iban'])
        );

        $validated['coefficient'] = $this->calculateCoefficient(
            (float) $validated['rate']
        );

        $overlap = TaxRate::query()
            ->where('local_government_id', $localGovernment->id)
            ->whereDate(
                'valid_from',
                '<=',
                $validated['valid_until'] ?? '9999-12-31'
            )
            ->where(function ($query) use ($validated) {
                $query->whereNull('valid_until')
                    ->orWhereDate(
                        'valid_until',
                        '>=',
                        $validated['valid_from']
                    );
            })
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'local_government_id' =>
                    'Za ovaj grad ili općinu već postoji porezna stopa koja se primjenjuje u odabranom razdoblju.',
            ]);
        }

        TaxRate::create($validated);

        return redirect()
            ->route('porezne-stope.index')
            ->with('success', 'Porezna stopa uspješno je spremljena.');
    }
}
