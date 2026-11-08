@extends('layouts.app')
@section('title', 'Detalji obračuna | Obračun drugog dohotka')
@push('page-styles')
<style>

        h1, .detail-card h2 { color: #17365c; }
        h1 { margin-bottom: 8px; }
        .report-id { color: #64748b; margin-bottom: 24px; }
        .details-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); grid-template-areas: "calculation organization referee"; gap: 20px; align-items: start; }
        .calculation-details { grid-area: calculation; }
        .organization-details { grid-area: organization; }
        .referee-details { grid-area: referee; }
        .detail-card { min-width: 0; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 24px; overflow-wrap: anywhere; }
        .detail-card h2 { margin-top: 0; }
        .amounts-divider { border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0; }
        .actions { margin-top: 20px; }
        .button { display: inline-block; padding: 12px 18px; margin: 0 10px 10px 0; color: #fff; text-decoration: none; border-radius: 6px; }
        .button-payment { background: #198754; }
        .button-back { background: #17365c; }
        .button-paid { background: #198754; }
        .status-paid { color: #198754; }
        @media (max-width: 900px) { .details-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 600px) { .details-grid { grid-template-columns: 1fr; } .detail-card { padding: 20px; } }
</style>
@endpush

@section('content')
<div class="container">
        <h1 class="app-page-title"><i class="bi bi-file-earmark-text" aria-hidden="true"></i> Detalji obračuna</h1>

        <p class="report-id">
            Identifikator izvješća:
            <strong>{{ $calculation->report_id }}</strong>
        </p>

        <div class="details-grid">
        <div class="detail-card organization-details app-card">
            <h2><i class="bi bi-building" aria-hidden="true"></i> Organizacija</h2>

            <p><strong>Naziv:</strong> {{ $calculation->organization_name ?? 'Nije navedeno' }}</p>
            <p><strong>Adresa:</strong> {{ $calculation->organization_address ?? 'Nije navedeno' }}</p>
            <p><strong>Poštanski broj i mjesto:</strong> {{ $calculation->organization_postal_code ?? '' }} {{ $calculation->organization_city ?? '' }}</p>
            <p><strong>OIB:</strong> {{ $calculation->organization_oib ?? 'Nije navedeno' }}</p>
            <p><strong>IBAN:</strong> {{ $calculation->organization_iban ?? 'Nije navedeno' }}</p>
        </div>

        <div class="detail-card referee-details app-card">
            <h2><i class="bi bi-person" aria-hidden="true"></i> Sudac / primatelj</h2>

            <p><strong>Ime i prezime:</strong> {{ $calculation->referee_first_name }} {{ $calculation->referee_last_name }}</p>
            <p><strong>OIB:</strong> {{ $calculation->referee_oib }}</p>
            <p><strong>Adresa:</strong> {{ $calculation->referee_address }}</p>
            <p><strong>Poštanski broj i mjesto:</strong> {{ $calculation->referee_postal_code }} {{ $calculation->referee_city }}</p>
            <p><strong>IBAN:</strong> {{ $calculation->referee_iban }}</p>
        </div>

        <div class="detail-card calculation-details app-card">
            <h2><i class="bi bi-calculator" aria-hidden="true"></i> Podaci obračuna</h2>

            <p><strong><i class="bi bi-calendar3" aria-hidden="true"></i> Datum isplate:</strong> {{ \Carbon\Carbon::parse($calculation->payment_date)->format('d.m.Y.') }}</p>
            <p><strong>Identifikator izvješća:</strong> {{ $calculation->report_id }}</p>
            <p><strong>Porezna stopa:</strong> {{ number_format((float) $calculation->tax_rate, 2, ',', '.') }} %</p>
            <p><strong>Koeficijent:</strong> {{ number_format((float) $calculation->coefficient, 6, ',', '.') }}</p>
            <p><strong>Mjesto obračuna poreza:</strong> {{ $calculation->tax_city }}</p>
            <p><strong>IBAN računa za uplatu poreza:</strong> {{ $calculation->tax_iban ?? 'Nije evidentiran' }}</p>
                
            <hr class="amounts-divider">

            <p><strong><i class="bi bi-currency-euro" aria-hidden="true"></i> Neto primitak:</strong> {{ number_format((float) $calculation->net_amount, 2, ',', '.') }} €</p>
            <p><strong><i class="bi bi-currency-euro" aria-hidden="true"></i> Bruto iznos:</strong> {{ number_format((float) $calculation->gross_amount, 2, ',', '.') }} €</p>
            <p><strong><i class="bi bi-currency-euro" aria-hidden="true"></i> Porez:</strong> {{ number_format((float) $calculation->tax_amount, 2, ',', '.') }} €</p>

            <p><strong><i class="bi bi-info-circle" aria-hidden="true"></i> Status:</strong>
                @if ($calculation->status === 'plaćeno')
                    <i class="bi bi-check-circle-fill status-paid" role="img" aria-label="Plaćeno"></i>
                @else
                    {{ $calculation->status }}
                @endif
            </p>
        </div>
        </div>
        <div class="actions">
        @if ($calculation->status !== 'plaćeno')
        <form method="POST" action="{{ route('calculations.mark-as-paid', $calculation) }}" style="display: inline-block;">
            @csrf
            <button type="submit" class="button button-paid icon-action app-button">
                <i class="bi bi-check-circle" aria-hidden="true"></i> Označi kao plaćeno
            </button>
        </form>
        @endif
        <a href="{{ route('calculations.payment-orders', $calculation) }}" class="button button-payment icon-action app-button">
            <i class="bi bi-credit-card" aria-hidden="true"></i> Nalozi za plaćanje
        </a>
        <a href="{{ route('calculations.index') }}" class="button button-back icon-action app-button">
            <i class="bi bi-arrow-left" aria-hidden="true"></i> Povratak na prethodne obračune
        </a>
        </div>
    </div>
@endsection
