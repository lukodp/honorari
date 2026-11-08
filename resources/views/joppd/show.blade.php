@extends('layouts.app')
@section('title', 'JOPPD priprema')
@push('page-styles')
<style>
    .joppd-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 20px; }
    .joppd-action--primary { color: #fff; background: #17365c; border: 1px solid #17365c; }
    .joppd-action--primary:hover { color: #fff; background: #254d7b; border-color: #254d7b; }
    .joppd-action--secondary { color: #17365c; background: #e2e8f0; border: 1px solid #cbd5e1; }
    .joppd-action--secondary:hover { color: #17365c; background: #cbd5e1; }
</style>
@endpush

@section('content')
<h1 class="app-page-title">JOPPD priprema</h1>
<section class="app-card" style="padding: 24px;">
    <p><strong>Organizacija:</strong> {{ $preparation->snapshot['header']['organization']['name'] }}</p>
    <p><strong>Datum isplate:</strong> {{ $preparation->payment_date->format('d.m.Y.') }}</p>
    <p><strong>Identifikator izvješća:</strong> {{ $preparation->snapshot['header']['report_id'] }}</p>
    <p><strong>Verzija:</strong> {{ $preparation->revision }} ({{ $preparation->status === 'ready' ? 'aktivna' : 'prethodna verzija' }})</p>
    <p><strong>Broj osoba/redaka:</strong> {{ $preparation->snapshot['summary']['people'] }} / {{ $preparation->snapshot['summary']['rows'] }}</p>
    <p><strong>Bruto / porez / neto:</strong> {{ $preparation->snapshot['summary']['gross'] }} € / {{ $preparation->snapshot['summary']['tax'] }} € / {{ $preparation->snapshot['summary']['net'] }} €</p>
    <div class="joppd-actions">
        <a class="app-button joppd-action--primary" href="{{ route('joppd.download', $preparation) }}">Preuzmi XML</a>
        <a class="app-button joppd-action--secondary" href="{{ route('joppd.create') }}">Nova priprema</a>
    </div>
    @if ($preparations->count() > 1)
        <h2>Verzije ove pripreme</h2>
        <ul>
            @foreach ($preparations as $version)
                <li>
                    <a href="{{ route('joppd.show', $version) }}">Verzija {{ $version->revision }} — {{ $version->status === 'ready' ? 'aktivna' : 'prethodna' }}</a>
                    · <a href="{{ route('joppd.download', $version) }}">Preuzmi XML</a>
                </li>
            @endforeach
        </ul>
    @endif
</section>
@endsection
