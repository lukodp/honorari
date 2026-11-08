@extends('layouts.app')
@section('title', 'Porezne stope - Obračun drugog dohotka')
@push('page-styles')
<style>


        h1 {
            margin-bottom: 8px;
        }

        .subtitle {
            color: #64748b;
            margin-bottom: 25px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .button {
            display: inline-block;
            padding: 11px 16px;
            background: #174a7e;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .button-secondary {
            background: #64748b;
        }

        .panel {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.05);
            overflow-x: auto;
        }

        .filter-form {
            display: flex;
            gap: 10px;
            margin-bottom: 18px;
        }

        .filter-form input {
            flex: 1;
            min-width: 0;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 13px 10px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: #f8fafc;
        }

        .message {
            padding: 12px 15px;
            margin-bottom: 18px;
            background: #dcfce7;
            color: #166534;
            border-radius: 6px;
        }

        .empty {
            padding: 25px 0;
            text-align: center;
            color: #64748b;
        }
</style>
@endpush

@section('content')
<div class="container">

    <h1 class="app-page-title"><i class="bi bi-percent" aria-hidden="true"></i> Porezne stope</h1>

    <p class="subtitle">
        Evidencija poreznih stopa i koeficijenata prema gradu ili općini
        te datumu primjene.
    </p>

    <p class="subtitle">
        Za izmjenu podataka dodajte poreznu stopu sa novim datumom primjene (od-do).
    </p>

    <div class="topbar">
        <a href="{{ url('/') }}" class="button button-secondary app-button">
            <i class="bi bi-arrow-left" aria-hidden="true"></i> Povratak na početnu
        </a>

        <a href="{{ route('porezne-stope.create') }}" class="button app-button">
            <i class="bi bi-plus-circle" aria-hidden="true"></i> Dodaj poreznu stopu
        </a>
    </div>

    @if (session('success'))
        <div class="message success-icon" role="status">
            <i class="bi bi-check-circle" aria-hidden="true"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="panel app-card">

        <form method="GET" action="{{ route('porezne-stope.index') }}" class="filter-form">
            <input
                type="search"
                name="search"
                value="{{ $search }}"
                placeholder="Pretraži grad ili općinu"
                aria-label="Pretraži grad ili općinu"
            >
            <button type="submit" class="button icon-action app-button"><i class="bi bi-search" aria-hidden="true"></i> Pretraži</button>
            @if ($search !== '')
                <a href="{{ route('porezne-stope.index') }}" class="button button-secondary app-button"><i class="bi bi-x-circle" aria-hidden="true"></i> Poništi</a>
            @endif
        </form>

        @if ($search !== '')
            <table>
            <thead>
                <tr>
                    <th><i class="bi bi-geo-alt" aria-hidden="true"></i> Grad ili općina</th>
                    <th><i class="bi bi-bank" aria-hidden="true"></i> IBAN računa</th>
                    <th><i class="bi bi-percent" aria-hidden="true"></i> Porezna stopa</th>
                    <th>Koeficijent</th>
                    <th><i class="bi bi-calendar3" aria-hidden="true"></i> Vrijedi od</th>
                    <th><i class="bi bi-calendar3" aria-hidden="true"></i> Vrijedi do</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($taxRates as $taxRate)
                    <tr>
                        <td>{{ $taxRate->city }}</td>
                        <td>{{ $taxRate->iban ?: 'Nije uneseno' }}</td>
                        <td>{{ number_format((float) $taxRate->rate, 2, ',', '.') }} %</td>
                        <td>{{ number_format((float) $taxRate->coefficient, 6, ',', '.') }}</td>
                        <td>{{ $taxRate->valid_from->format('d.m.Y.') }}</td>
                        <td>
                            {{ $taxRate->valid_until
                                ? $taxRate->valid_until->format('d.m.Y.')
                                : 'Nije određeno' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty">
                            Još nema evidentiranih poreznih stopa.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            </table>
        @endif

    </div>
</div>
@endsection
