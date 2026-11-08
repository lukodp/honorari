<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\Organization;
use App\Models\Referee;
use App\Models\TaxRate;
use App\Services\Hub3BarcodeService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalculationController extends Controller
{
    public function index(Request $request)
    {
        $query = Calculation::query();

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('report_id', 'like', '%'.$search.'%')
                    ->orWhere('organization_name', 'like', '%'.$search.'%')
                    ->orWhere('referee_first_name', 'like', '%'.$search.'%')
                    ->orWhere('referee_last_name', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('organization')) {
            $query->where(
                'organization_name',
                $request->input('organization')
            );
        }

        if ($request->filled('date_from')) {
            $query->whereDate(
                'payment_date',
                '>=',
                $request->input('date_from')
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'payment_date',
                '<=',
                $request->input('date_to')
            );
        }

        $calculations = $query
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $organizations = Organization::query()
            ->orderBy('name')
            ->get(['name'])
            ->unique('name')
            ->values();

        return view('calculations.index', compact(
            'calculations',
            'organizations'
        ));
    }

    public function show(Calculation $calculation)
    {
        return view('calculations.show', compact('calculation'));
    }

    public function markAsPaid(Calculation $calculation)
    {
        $calculation->update(['status' => 'plaćeno']);

        return redirect()
            ->route('calculations.show', $calculation)
            ->with('success', 'Obračun je označen kao plaćen.');
    }

    public function destroy(Calculation $calculation)
    {
        $calculation->delete();

        return redirect()
            ->route('calculations.index')
            ->with('success', 'Obračun je uspješno obrisan.');
    }

    public function paymentOrders(
        Calculation $calculation,
        Hub3BarcodeService $hub3
    ) {
        $honorariumBarcode = $hub3->forHonorarium($calculation);
        $taxBarcode = $hub3->forTax($calculation);

        return view('calculations.payment-orders', compact(
            'calculation',
            'honorariumBarcode',
            'taxBarcode'
        ));
    }

    public function create()
    {
        $referees = Referee::query()
            ->where('is_active', true)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('calculations.create', compact('referees'));
    }

    public function preview(Request $request)
    {
        $validated = $request->validate([
            'referee_id' => ['required', 'integer', 'exists:referees,id'],
            'payment_date' => ['required', 'date', 'after_or_equal:today'],
            'net_amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
        ], [
            'net_amount.required' => 'Unesite neto primitak.',
            'net_amount.numeric' => 'Neto primitak mora biti broj.',
            'net_amount.gt' => 'Neto primitak mora biti veći od nule.',
            'net_amount.decimal' => 'Neto primitak može imati najviše dvije decimale.',
        ]);

        $referee = Referee::findOrFail($validated['referee_id']);

        if (! $referee->is_active) {
            return back()
                ->withErrors([
                    'referee_id' => 'Odabrani sudac nije aktivan.',
                ])
                ->withInput();
        }

        $taxRate = TaxRate::findApplicable(
            $referee->local_government_id,
            $validated['payment_date']
        );

        if (! $taxRate) {
            return back()
                ->withErrors([
                    'referee_id' => 'Za grad ili općinu prebivališta suca ne postoji porezna stopa koja vrijedi na odabrani datum isplate.',
                ])
                ->withInput();
        }

        $netAmount = (float) $validated['net_amount'];
        $coefficient = (float) $taxRate->coefficient;
        $rate = (float) $taxRate->rate / 100;

        $grossAmount = round($netAmount * $coefficient, 2);
        $taxAmount = round($grossAmount * $rate, 2);

        return view('calculations.preview', compact(
            'referee',
            'taxRate',
            'validated',
            'netAmount',
            'grossAmount',
            'taxAmount'
        ));
    }

    private function generateReportId(string $paymentDate): string
    {
        $date = Carbon::parse($paymentDate);

        $year = $date->format('y');
        $dayOfYear = $date->dayOfYear;

        return $year.str_pad(
            (string) $dayOfYear,
            3,
            '0',
            STR_PAD_LEFT
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'referee_id' => ['required', 'integer', 'exists:referees,id'],
            'payment_date' => ['required', 'date', 'after_or_equal:today'],
            'net_amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
        ]);

        $referee = Referee::findOrFail($validated['referee_id']);

        if (! $referee->is_active) {
            return back()
                ->withErrors([
                    'referee_id' => 'Odabrani sudac nije aktivan.',
                ])
                ->withInput();
        }

        $taxRate = TaxRate::findApplicable(
            $referee->local_government_id,
            $validated['payment_date']
        );

        if (! $taxRate) {
            return back()
                ->withErrors([
                    'referee_id' => 'Za grad ili općinu prebivališta suca ne postoji porezna stopa koja vrijedi na odabrani datum isplate.',
                ])
                ->withInput();
        }

        $organization = Organization::query()
            ->where('is_active', true)
            ->first();

        if (! $organization) {
            return back()
                ->withErrors([
                    'organization' => 'Najprije unesite podatke organizacije.',
                ])
                ->withInput();
        }

        $netAmount = (float) $validated['net_amount'];
        $coefficient = (float) $taxRate->coefficient;
        $rate = (float) $taxRate->rate / 100;

        $grossAmount = round($netAmount * $coefficient, 2);
        $taxAmount = round($grossAmount * $rate, 2);

        $paymentDate = Carbon::parse($validated['payment_date']);
        $reportId = $this->generateReportId($validated['payment_date']);

        $calculation = Calculation::create([
            'report_id' => $reportId,

            'organization_name' => $organization->name,
            'organization_address' => trim($organization->street.' '.$organization->house_number),
            'organization_postal_code' => $organization->postal_code,
            'organization_city' => $organization->city,
            'organization_oib' => $organization->oib,
            'organization_iban' => $organization->iban,

            'referee_id' => $referee->id,
            'referee_first_name' => $referee->first_name,
            'referee_last_name' => $referee->last_name,
            'referee_oib' => $referee->oib,
            'referee_address' => $referee->address,
            'referee_postal_code' => $referee->postal_code,
            'referee_city' => $referee->city,
            'referee_iban' => $referee->iban,
            'referee_local_government_code' => $referee->localGovernment?->code,

            'payment_date' => $validated['payment_date'],

            'net_amount' => $netAmount,
            'gross_amount' => $grossAmount,
            'tax_rate' => $taxRate->rate,
            'coefficient' => $taxRate->coefficient,
            'tax_amount' => $taxAmount,
            'tax_iban' => $taxRate->iban,
            'tax_city' => $taxRate->city,
            'tax_valid_from' => $taxRate->valid_from,
            'tax_valid_until' => $taxRate->valid_until,

            'status' => 'draft',
        ]);

        return redirect()
            ->route('calculations.create')
            ->with(
                'success',
                'Obračun je uspješno spremljen. Identifikator izvješća: '
                    .$calculation->report_id
            );
    }
}
