@extends('layouts.app')
@section('title', 'Pregled obračuna | Obračun drugog dohotka')
@push('page-styles')
<style>


        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #174a7e;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .page-header {
            margin-bottom: 24px;
        }

        .page-header h1 {
            margin: 0 0 8px;
            color: #17365c;
            font-size: 28px;
        }

        .page-description {
            margin: 0;
            color: #64748b;
        }

        .panel {
            margin-bottom: 22px;
            padding: 26px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(23, 54, 92, 0.06);
        }

        .panel h2 {
            margin: 0 0 20px;
            color: #17365c;
            font-size: 20px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .detail-label {
            margin-bottom: 4px;
            color: #64748b;
            font-size: 13px;
        }

        .detail-value {
            color: #263445;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .amount-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .amount-card {
            padding: 18px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
        }

        .amount-card.primary {
            background: #edf4fb;
            border-color: #c5d9ed;
        }

        .amount-label {
            margin-bottom: 8px;
            color: #64748b;
            font-size: 13px;
        }

        .amount-value {
            color: #17365c;
            font-size: 23px;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .tax-row {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 13px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .tax-row:last-child {
            border-bottom: none;
        }

        .tax-row strong {
            text-align: right;
        }

        .notice {
            padding: 14px 16px;
            color: #475569;
            background: #f8fafc;
            border-left: 4px solid #174a7e;
            border-radius: 4px;
            font-size: 13px;
        }

        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: 24px;
        }

        .button {
            display: inline-block;
            padding: 11px 20px;
            border-radius: 7px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
        }

        .button-secondary {
            color: #17365c;
            background: #ffffff;
            border: 1px solid #cbd5e1;
        }

        .button-primary {
            color: #ffffff;
            background: #17365c;
            border: 1px solid #17365c;
        }

        .button:hover {
            opacity: 0.9;
        }

        @media (max-width: 640px) {


            .page-header h1 {
                font-size: 24px;
            }

            .panel {
                padding: 20px 16px;
            }

            .details-grid {
                grid-template-columns: 1fr;
            }

            .amount-grid {
                grid-template-columns: 1fr;
            }

            .actions {
                flex-direction: column;
                align-items: stretch;
            }

            .button {
                width: 100%;
            }
        }


    /* Styles migrated from inline attributes */
    .view-style-1 { margin-top: 28px; }
</style>
@endpush

@section('content')
<a class="back-link" href="{{ route('calculations.create') }}">
            <i class="bi bi-arrow-left" aria-hidden="true"></i> Povratak na novi obračun
        </a>

        <header class="page-header">
            <h1 class="app-page-title"><i class="bi bi-eye" aria-hidden="true"></i> Pregled obračuna</h1>
            <p class="page-description">
                Provjerite podatke primatelja i izračunate iznose.
            </p>
        </header>

        <section class="panel app-card">
            <h2><i class="bi bi-person" aria-hidden="true"></i> Podaci o primatelju</h2>

            <div class="details-grid">
                <div>
                    <div class="detail-label">Ime i prezime</div>
                    <div class="detail-value">
                        {{ $referee->first_name }} {{ $referee->last_name }}
                    </div>
                </div>

                <div>
                    <div class="detail-label">OIB</div>
                    <div class="detail-value">{{ $referee->oib }}</div>
                </div>

                <div>
                    <div class="detail-label">Mjesto prebivališta</div>
                    <div class="detail-value">{{ $referee->city }}</div>
                </div>

                <div>
                    <div class="detail-label"><i class="bi bi-calendar3" aria-hidden="true"></i> Datum isplate</div>
                    <div class="detail-value">
                        {{ \Carbon\Carbon::parse($validated['payment_date'])->format('d.m.Y.') }}
                    </div>
                </div>
            </div>
        </section>

        <section class="panel app-card">
            <h2><i class="bi bi-currency-euro" aria-hidden="true"></i> Obračun primitka</h2>

            <div class="amount-grid">
                <div class="amount-card primary">
                    <div class="amount-label"><i class="bi bi-currency-euro" aria-hidden="true"></i> Neto primitak</div>
                    <div class="amount-value">
                        {{ number_format($netAmount, 2, ',', '.') }} €
                    </div>
                </div>

                <div class="amount-card">
                    <div class="amount-label"><i class="bi bi-currency-euro" aria-hidden="true"></i> Bruto iznos</div>
                    <div class="amount-value">
                        {{ number_format($grossAmount, 2, ',', '.') }} €
                    </div>
                </div>

                <div class="amount-card">
                    <div class="amount-label"><i class="bi bi-currency-euro" aria-hidden="true"></i> Porez</div>
                    <div class="amount-value">
                        {{ number_format($taxAmount, 2, ',', '.') }} €
                    </div>
                </div>
            </div>

            <h2 class="view-style-1"><i class="bi bi-sliders" aria-hidden="true"></i> Parametri obračuna</h2>

            <div class="tax-row">
                <span>Porezna stopa</span>
                <strong>
                    {{ number_format((float) $taxRate->rate, 2, ',', '.') }} %
                </strong>
            </div>

            <div class="tax-row">
                <span>Koeficijent</span>
                <strong>
                    {{ number_format((float) $taxRate->coefficient, 6, ',', '.') }}
                </strong>
            </div>

            <div class="tax-row">
                <span>Neto za isplatu</span>
                <strong>
                    {{ number_format($netAmount, 2, ',', '.') }} €
                </strong>
            </div>
        </section>

        <div class="notice notice-icon" role="status">
            <i class="bi bi-info-circle" aria-hidden="true"></i>
            Ovo je pregled izračuna. Obračun još nije trajno spremljen u evidenciju
            i nalozi za plaćanje još nisu generirani.
        </div>

        <div class="actions">
            <a
                class="button button-secondary app-button"
                href="{{ route('calculations.create') }}"
            >
                <i class="bi bi-pencil" aria-hidden="true"></i> Izmijeni unos
            </a>

            <form action="{{ route('calculations.store') }}" method="POST">
                @csrf

                <input
                    type="hidden"
                    name="referee_id"
                    value="{{ $referee->id }}"
                >

                <input
                    type="hidden"
                    name="payment_date"
                    value="{{ $validated['payment_date'] }}"
                >

                <input
                    type="hidden"
                    name="net_amount"
                    value="{{ $validated['net_amount'] }}"
                >

                <button type="submit" class="button button-primary app-button">
                    <i class="bi bi-check-circle" aria-hidden="true"></i> Spremi obračun
                </button>
            </form>

            <a class="button button-secondary app-button" href="{{ url('/') }}">
                <i class="bi bi-arrow-left" aria-hidden="true"></i> Povratak na početnu
            </a>
        </div>
@endsection
