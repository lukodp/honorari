<?php

namespace App\Http\Controllers;

use App\Models\JoppdPreparation;
use App\Services\JoppdPreparationService;
use App\Services\JoppdXmlService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class JoppdController extends Controller
{
    public function create(): View
    {
        return view('joppd.create');
    }

    public function preview(Request $request, JoppdPreparationService $service): View
    {
        $validated = $request->validate([
            'payment_date' => ['required', 'date_format:Y-m-d'],
        ], [
            'payment_date.required' => 'Odaberite datum isplate.',
            'payment_date.date_format' => 'Datum isplate nije valjan.',
        ]);

        $inspection = $service->inspect($validated['payment_date']);
        $xmlValidation = $service->validateInspection($inspection, $validated['payment_date']);
        $inspection['errors'] = array_merge($inspection['errors'], $xmlValidation['errors']);
        $inspection['warnings'] = $xmlValidation['warnings'];

        return view('joppd.create', [
            'paymentDate' => $validated['payment_date'],
            'inspection' => $inspection,
        ]);
    }

    public function prepare(Request $request, JoppdPreparationService $service): RedirectResponse
    {
        $validated = $request->validate([
            'payment_date' => ['required', 'date_format:Y-m-d'],
        ]);

        try {
            $preparation = $service->prepare($validated['payment_date']);
        } catch (\DomainException $exception) {
            return redirect()
                ->route('joppd.create')
                ->withErrors(['joppd' => $exception->getMessage()])
                ->withInput();
        }

        return redirect()
            ->route('joppd.show', $preparation)
            ->with('success', 'JOPPD priprema je spremljena. XML možete preuzeti ili ponovno preuzeti iz ove pripreme.');
    }

    public function show(JoppdPreparation $preparation): View
    {
        $preparations = JoppdPreparation::query()
            ->where('organization_oib', $preparation->organization_oib)
            ->whereDate('payment_date', $preparation->payment_date)
            ->orderByDesc('revision')
            ->get();

        return view('joppd.show', compact('preparation', 'preparations'));
    }

    public function download(JoppdPreparation $preparation, JoppdXmlService $xmlService): Response
    {
        $xml = $xmlService->generate($preparation);
        $date = $preparation->payment_date->format('Ymd');
        $fileName = sprintf(
            'JOPPD_%s_%s-%s_r%s.xml',
            $preparation->organization_oib,
            $date,
            $date,
            $preparation->revision
        );

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
