@extends('layouts.app')
@section('title', 'Kreiraj JOPPD obrazac')
@push('page-styles')
<style>
    .joppd-message { margin: 16px 0; padding: 14px 18px; border: 1px solid; border-radius: 8px; }
    .joppd-message--error { color: #991b1b; background: #fef2f2; border-color: #fecaca; }
    .joppd-message--success { color: #166534; background: #f0fdf4; border-color: #bbf7d0; }
    .joppd-table { width: 100%; border-collapse: collapse; }
    .joppd-table th, .joppd-table td { padding: 9px 12px; border-bottom: 1px solid #e2e8f0; text-align: left; white-space: nowrap; }
    .joppd-table th { color: #17365c; background: #f1f5f9; }
</style>
@endpush

@section('content')
<h1 class="app-page-title"><i class="bi bi-file-earmark-code" aria-hidden="true"></i> Kreiraj JOPPD obrazac</h1>
<p>Odaberite datum isplate. U pripremu će se automatski uključiti svi spremljeni obračuni za taj datum i aktivnu organizaciju.</p>

@if (session('success'))
    <div class="joppd-message joppd-message--success" role="status">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="joppd-message joppd-message--error" role="alert">
        <strong>Priprema nije izrađena:</strong>
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<section class="app-card" style="padding: 24px; margin-bottom: 24px;">
    <form method="POST" action="{{ route('joppd.preview') }}">
        @csrf
        <label for="payment_date">Datum isplate</label>
        <input id="payment_date" name="payment_date" type="date" value="{{ old('payment_date', $paymentDate ?? '') }}" required>
        <button class="app-button" type="submit">Prikaži obračune</button>
    </form>
</section>

@isset($inspection)
    <section class="app-card" style="padding: 24px;">
        <h2>Pregled skupa</h2>
        <p><strong>Datum isplate:</strong> {{ \Carbon\Carbon::parse($paymentDate)->format('d.m.Y.') }}</p>
        <p><strong>Aktivna organizacija:</strong> {{ $inspection['organization']?->name ?? 'Nije utvrđena' }}</p>
        <p><strong>Pronađeni obračuni:</strong> {{ $inspection['calculations']->count() }}</p>
        <p><strong>Osobe / redci:</strong> {{ $inspection['summary']['people'] }} / {{ $inspection['summary']['rows'] }}</p>
        <p><strong>Bruto:</strong> {{ number_format((float) $inspection['summary']['gross'], 2, ',', '.') }} €</p>
        <p><strong>Porez:</strong> {{ number_format((float) $inspection['summary']['tax'], 2, ',', '.') }} €</p>
        <p><strong>Neto:</strong> {{ number_format((float) $inspection['summary']['net'], 2, ',', '.') }} €</p>

        @if ($inspection['errors'] !== [])
            <div class="joppd-message joppd-message--error" role="alert">
                <strong>Potrebno je riješiti sljedeće probleme:</strong>
                <ul>@foreach ($inspection['errors'] as $message)<li>{{ $message }}</li>@endforeach</ul>
            </div>
        @else
            <h3>Uključeni obračuni</h3>
            <div style="overflow-x: auto;">
                <table class="joppd-table">
                    <thead><tr><th>Primatelj</th><th>OIB</th><th>Općina (P2)</th><th>Izvješće</th><th>Bruto</th><th>Porez</th><th>Neto</th></tr></thead>
                    <tbody>
                    @foreach ($inspection['calculations'] as $calculation)
                        <tr>
                            <td>{{ $calculation->referee_first_name }} {{ $calculation->referee_last_name }}</td>
                            <td>{{ $calculation->referee_oib }}</td>
                            <td>{{ $inspection['rows'][$loop->index]['P2'] ?? '—' }}</td>
                            <td>{{ $calculation->report_id }}</td>
                            <td>{{ number_format((float) $calculation->gross_amount, 2, ',', '.') }} €</td>
                            <td>{{ number_format((float) $calculation->tax_amount, 2, ',', '.') }} €</td>
                            <td>{{ number_format((float) $calculation->net_amount, 2, ',', '.') }} €</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <form method="POST" action="{{ route('joppd.prepare') }}" style="margin-top: 20px;">
                @csrf
                <input type="hidden" name="payment_date" value="{{ $paymentDate }}">
                <button class="app-button" type="submit">Spremi pripremu i preuzmi XML</button>
            </form>
        @endif
    </section>
@endisset
@endsection
