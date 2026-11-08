@extends('layouts.app')
@section('title', 'Nalozi za plaćanje | Obračun drugog dohotka')
@section('body-class', 'view-style-1')
@push('page-styles')
<style>
@media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }

            .no-print {
                display: none !important;
            }

            .payment-card {
                break-inside: avoid;
                page-break-inside: avoid;
                box-shadow: none !important;
            }

            h1, h2, h3, p {
                color: #000000 !important;
            }
        }
        @page {
    size: A4 portrait;
    margin: 10mm;
}

    @media print {
        body {
            font-size: 11px !important;
        }

        h1 {
            font-size: 20px !important;
            margin-top: 0 !important;
        }

        h2 {
            font-size: 15px !important;
        }

        h3 {
            font-size: 12px !important;
        }

        .payment-card {
            padding: 12px !important;
            margin-bottom: 10px !important;
        }

        .payment-card p {
            margin: 5px 0 !important;
        }
            .payment-card h2 {
        margin-bottom: 8px !important;
    }

    .payment-card p {
        margin: 3px 0 !important;
        line-height: 1.2 !important;
    }

    .payment-card h3 {
        margin-bottom: 8px !important;
    }

    .payment-card > div {
        margin-top: 12px !important;
        padding: 10px !important;
    }       
    }
    .payment-cards {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
    align-items: start;
    width: 100%;
    }

    .payment-card {
        min-width: 0;
        box-sizing: border-box;
    }

    @media (max-width: 900px) {
        .payment-cards {
            grid-template-columns: 1fr;
        }
    }

    @media print {
        .payment-cards {
            display: block !important;
        }
    }

    .payment-layout > h1,
    .payment-layout > p,
    .payment-layout > a,
    .payment-layout > button {
        grid-column: 1 / -1;
    }

    @media (max-width: 900px) {
        .payment-layout {
            grid-template-columns: 1fr;
        }
    }

    @media print {
        .payment-layout {
            display: block !important;
        }
    }
    @media screen {
    .payment-card svg {
        width: 100% !important;
        max-width: 100% !important;
        height: auto !important;
        display: block;
    }
}
    }


    /* Styles migrated from inline attributes */
    .view-style-1 { margin: 0; padding: 30px; background: #f1f5f9; font-family: Arial, Helvetica, sans-serif; color: #1e293b; }

    .view-style-3 { color: #17365c; margin-bottom: 8px; }
    .view-style-4 { color: #64748b; margin-bottom: 24px; }
    .view-style-5 { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 24px; margin-bottom: 20px; }
    .view-style-6 { color: #17365c; margin-top: 0; }
    .view-style-7 { color:red }
    .view-style-8 { margin-top: 24px; padding: 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; }
    .view-style-9 { margin-top: 0; color: #17365c; }
    .view-style-10 { width: 100%; overflow-x: auto; overflow-y: visible; }
    .view-style-11 { font-size: 13px; color: #64748b; }
    .view-style-12 { display: inline-block; padding: 12px 18px; margin-right: 10px; background: #198754; color: #ffffff; border: none; text-decoration: none; border-radius: 6px; cursor: pointer; font-size: 14px; }
    .view-style-13 { display: inline-block; padding: 12px 18px; background: #17365c; color: #ffffff; text-decoration: none; border-radius: 6px; }
</style>
@endpush

@section('content')
<div class="payment-layout view-style-2">

        <h1 class="view-style-3 app-page-title">Nalozi za plaćanje</h1>

        <p class="view-style-4">
            Identifikator izvješća:
            <strong>{{ $calculation->report_id }}</strong>
        </p>
        <div class="payment-cards">
            <div class="payment-card view-style-5 app-card">
                <h2 class="view-style-6">1. Isplata honorara</h2>

                <p><strong>Platitelj:</strong> {{ $calculation->organization_name }}</p>
                <p><strong>Primatelj:</strong> {{ $calculation->referee_first_name }} {{ $calculation->referee_last_name }}</p>
                <p><strong>Adresa primatelja:</strong> {{ $calculation->referee_address }}</p>
                <p><strong>IBAN primatelja:</strong> {{ $calculation->referee_iban }}</p>
                <p><strong>Iznos:</strong> {{ number_format((float) $calculation->net_amount, 2, ',', '.') }} €</p>
                <p><strong>Model primatelja:</strong> HR69 <strong>Poziv na broj primatelja:</strong> 40002-{{ $calculation->organization_oib }}-130</p>
                <p class="view-style-7"><strong>Model i poziv na broj platitelja:</strong> HR67 {{ $calculation->organization_oib }}-{{ $calculation->report_id }}-8<br><span>Unijeti ručno!</span></p>
                <p><strong>Opis plaćanja:</strong> Isplata honorara za izvješće {{ $calculation->report_id }}</p>
                <div class="view-style-8">
                    <h3 class="view-style-9">Barkod za mobilno bankarstvo</h3>
                    <div class="view-style-10">
                        {!! $honorariumBarcode !!}                    
                    </div>
                    <p class="view-style-11">
                        Skenirajte barkod mobilnom bankovnom aplikacijom i provjerite sve podatke prije potvrde plaćanja.
                    </p>
                </div>
            </div>

            <div class="payment-card view-style-5 app-card">
                <h2 class="view-style-6">2. Uplata poreza</h2>

                <p><strong>Platitelj:</strong> {{ $calculation->organization_name }}</p>
                <p><strong>IBAN platitelja:</strong> {{ $calculation->organization_iban }}</p>
                <p><strong>Primatelj:</strong> {{ $calculation->tax_city }}</p>
                <p><strong>IBAN primatelja:</strong> {{ $calculation->tax_iban }}</p>
                <p><strong>Iznos:</strong> {{ number_format((float) $calculation->tax_amount, 2, ',', '.') }} €</p>
                <p><strong>Model primatelja:</strong> HR68</p>
                <p><strong>Poziv na broj primatelja:</strong> 1945-{{ $calculation->organization_oib }}-{{ $calculation->report_id }}</p>
                <p><strong>Opis plaćanja:</strong> Porez na honorar za izvješće {{ $calculation->report_id }}</p>
                <div class="view-style-8">
                    <h3 class="view-style-9">Barkod za mobilno bankarstvo</h3>
                    <div class="view-style-10">
                        {!! $taxBarcode !!}
                    </div>
                    <p class="view-style-11">
                        Skenirajte barkod mobilnom bankovnom aplikacijom i provjerite sve podatke prije potvrde plaćanja.
                    </p>
                </div>
            </div>
        </div>

        <button class="no-print view-style-12 app-button" onclick="window.print()">
            Ispiši naloge
        </button>
        <a class="no-print view-style-13 app-button" href="{{ route('calculations.show', $calculation) }}">
            Povratak na obračun
        </a>

    </div>
@endsection
