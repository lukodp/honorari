@extends('layouts.app')
@section('title', 'Prethodni obračuni | Obračun drugog dohotka')
@section('body-class', 'view-style-1')
@push('page-styles')
<style>
/* Styles migrated from inline attributes */
    .view-style-1 { margin: 0; padding: 30px; background: #f1f5f9; font-family: Arial, sans-serif; color: #1e293b; }

    .view-style-3 { color: #17365c; margin-bottom: 8px; }
    .view-style-4 { color: #64748b; margin-bottom: 24px; }
    .view-style-5 { margin-bottom: 24px; padding: 20px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; }
    .view-style-6 { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }
    .view-style-7 { display: block; margin-bottom: 6px; font-weight: 600; }
    .view-style-8 { box-sizing: border-box; width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 5px; }
    .view-style-9 { display: flex; gap: 10px; margin-top: 18px; flex-wrap: wrap; }
    .view-style-10 { padding: 10px 16px; background: #17365c; color: #ffffff; border: 0; border-radius: 5px; cursor: pointer; }
    .view-style-11 { display: inline-block; padding: 10px 16px; background: #e2e8f0; color: #17365c; text-decoration: none; border-radius: 5px; }
    .view-style-12 { margin: 0 0 16px; padding: 12px 16px; background: #dcfce7; color: #166534; border-radius: 6px; }
    .view-style-13 { overflow-x: auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; }
    .view-style-14 { width: 100%; border-collapse: collapse; text-align: left; white-space: nowrap; }
    .view-style-15 { background: #17365c; color: #ffffff; }
    .view-style-16 { padding: 14px; }
    .view-style-17 { padding: 14px; text-align: right; }
    .view-style-18 { border-bottom: 1px solid #e2e8f0; }
    .view-style-19 { display: inline-block; padding: 8px 12px; background: #17365c; color: #ffffff; text-decoration: none; border-radius: 5px; }
    .view-style-20 { display: inline-block; margin-left: 6px; }
    .view-style-21 { padding: 8px 12px; background: #b91c1c; color: #ffffff; border: 0; border-radius: 5px; cursor: pointer; }
    .view-style-22 { padding: 24px; text-align: center; color: #64748b; }
    .view-style-23 { margin-top: 20px; color: #64748b; }
    .view-style-24 { margin-top: 12px; }
    .status-paid { color: #198754; }
    .status-legend { box-sizing: border-box; width: 30%; margin: 12px 0 0 auto; padding: 12px; color: #64748b; text-align: left; background: rgba(255, 255, 255, 0.5); border-radius: 6px; }
    .view-style-25 { margin-top: 24px; }
    .view-style-26 { display: inline-block; padding: 12px 18px; background: #17365c; color: #ffffff; text-decoration: none; border-radius: 6px; }
</style>
@endpush

@section('content')
<div class="view-style-2">
        <h1 class="view-style-3 app-page-title"><i class="bi bi-file-earmark-text" aria-hidden="true"></i> Prethodni obračuni</h1>

        <p class="view-style-4">
            Pregled svih spremljenih obračuna, neovisno o organizaciji.
        </p>

        <form method="GET" action="{{ route('calculations.index') }}"
            class="view-style-5">

            <div class="view-style-6">

                <div>
                    <label for="search" class="view-style-7">
                        Pretraživanje
                    </label>
                    <input type="text" id="search" name="search"
                        value="{{ request('search') }}"
                        placeholder="Identifikator, organizacija ili sudac"
                        class="view-style-8">
                </div>

                <div>
                    <label for="organization" class="view-style-7">
                        Organizacija
                    </label>
                    <select id="organization" name="organization"
                            class="view-style-8">
                        <option value="">Sve organizacije</option>

                        @foreach ($organizations as $organization)
                            <option value="{{ $organization->name }}"
                                {{ request('organization') === $organization->name ? 'selected' : '' }}>
                                {{ $organization->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="date_from" class="view-style-7">
                        Datum isplate od
                    </label>
                    <input type="date" id="date_from" name="date_from"
                        value="{{ request('date_from') }}"
                        class="view-style-8">
                </div>

                <div>
                    <label for="date_to" class="view-style-7">
                        Datum isplate do
                    </label>
                    <input type="date" id="date_to" name="date_to"
                        value="{{ request('date_to') }}"
                        class="view-style-8">
                </div>

            </div>

            <div class="view-style-9">
                <button type="submit"
                        class="view-style-10 app-button">
                        <i class="bi bi-search" aria-hidden="true"></i> Pretraži
                </button>

                <a href="{{ route('calculations.index') }}"
                class="view-style-11 app-button">
                    <i class="bi bi-x-circle" aria-hidden="true"></i> Poništi filtre
                </a>
            </div>

        </form>

        @if (session('success'))
            <div class="view-style-12 success-icon" role="status">
                <i class="bi bi-check-circle" aria-hidden="true"></i>
                {{ session('success') }}
            </div>
        @endif

        <div class="view-style-13">
            <table class="view-style-14">
                <thead class="view-style-15">
                    <tr>
                        <th class="view-style-16">Identifikator</th>
                        <th class="view-style-16"><i class="bi bi-calendar3" aria-hidden="true"></i> Datum isplate</th>
                        <th class="view-style-16">Organizacija</th>
                        <th class="view-style-16">Sudac</th>
                        <th class="view-style-17"><i class="bi bi-currency-euro" aria-hidden="true"></i> Neto (€)</th>
                        <th class="view-style-17"><i class="bi bi-currency-euro" aria-hidden="true"></i> Bruto (€)</th>
                        <th class="view-style-17"><i class="bi bi-currency-euro" aria-hidden="true"></i> Porez (€)</th>
                        <th class="view-style-16">Status</th>
                        <th class="view-style-16">Akcija</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($calculations as $calculation)
                        <tr class="view-style-18">
                            <td class="view-style-16">
                                {{ $calculation->report_id }}
                            </td>

                            <td class="view-style-16">
                                {{ \Carbon\Carbon::parse($calculation->payment_date)->format('d.m.Y.') }}
                            </td>

                            <td class="view-style-16">
                                {{ $calculation->organization_name ?? 'Nije navedena' }}
                            </td>

                            <td class="view-style-16">
                                {{ $calculation->referee_first_name }}
                                {{ $calculation->referee_last_name }}
                            </td>

                            <td class="view-style-17">
                                {{ number_format((float) $calculation->net_amount, 2, ',', '.') }}
                            </td>

                            <td class="view-style-17">
                                {{ number_format((float) $calculation->gross_amount, 2, ',', '.') }}
                            </td>

                            <td class="view-style-17">
                                {{ number_format((float) $calculation->tax_amount, 2, ',', '.') }}
                            </td>

                            <td class="view-style-16">
                                @if (in_array($calculation->status, ['plaćeno', 'plačeno'], true))
                                    <i class="bi bi-check-circle-fill status-paid" role="img" aria-label="Plaćeno"></i>
                                @else
                                    {{ $calculation->status }}
                                @endif
                            </td>

                            <td class="view-style-16">
                                <a href="{{ route('calculations.show', $calculation) }}"
                                class="view-style-19 app-button">
                                    <i class="bi bi-eye" aria-hidden="true"></i> Detalji
                                </a>
                                <form method="POST"
                                      action="{{ route('calculations.destroy', $calculation) }}"
                                      class="view-style-20"
                                      onsubmit="return confirm('Trajno obrisati obračun {{ $calculation->report_id }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="view-style-21 app-button">
                                        <i class="bi bi-trash" aria-hidden="true"></i> Obriši
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="view-style-22">
                                Još nema spremljenih obračuna.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="view-style-23">
            Stranica {{ $calculations->currentPage() }} od {{ $calculations->lastPage() }}
        </div>

        <div class="view-style-24">
            {{ $calculations->links() }}
        </div>

        <div class="status-legend" aria-label="Opis statusa">
            <div><strong>Opis statusa:</strong></div>
            <div>draft = nacrt obračuna</div>
            <div><i class="bi bi-check-circle-fill status-paid" role="img" aria-label="Plaćeno"></i> = nalozi su plaćeni</div>
        </div>

        <div class="view-style-25">
            <a href="{{ url('/') }}"
               class="view-style-26">
                <i class="bi bi-arrow-left" aria-hidden="true"></i> Povratak na početnu
            </a>
        </div>
    </div>
@endsection
