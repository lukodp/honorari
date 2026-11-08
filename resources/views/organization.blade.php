@extends('layouts.app')
@section('title', 'Podaci organizacije | Obračun drugog dohotka')
@push('page-styles')
<style>



        .page-context {
            max-width: 800px;
            margin: 24px auto -20px;
            padding: 0 24px;
            color: #64748b;
            font-size: 14px;
        }


        .panel {
            background: white;
            border: 1px solid #e0e6ee;
            border-radius: 10px;
            padding: 28px;
            box-shadow: 0 3px 10px #152b4510;
        }

        .panel h2 {
            margin-top: 0;
        }

        .field {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 15px;
        }

        input:focus {
            outline: 2px solid #8bb6e8;
            border-color: #356da8;
        }

        .help {
            margin-top: 6px;
            color: #64748b;
            font-size: 13px;
        }

        .error {
            margin-top: 6px;
            color: #b42318;
            font-size: 13px;
        }

        .success {
            padding: 12px 16px;
            margin-bottom: 20px;
            border-radius: 6px;
            background: #e7f6ec;
            color: #176536;
        }

        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 28px;
        }

        .button {
            display: inline-block;
            padding: 12px 18px;
            border: none;
            border-radius: 6px;
            background: #17365c;
            color: white;
            font-size: 15px;
            text-decoration: none;
            cursor: pointer;
        }

        .button:hover {
            background: #254d7b;
        }

        .secondary {
            background: #e8edf3;
            color: #243247;
        }

        .secondary:hover {
            background: #d8e0e9;
        }

</style>
@endpush

@section('content')
<p class="page-context">Postavke organizacije / isplatitelja</p>

    <h2 class="app-page-title"><i class="bi bi-building" aria-hidden="true"></i> Podaci organizacije</h2>

    <div class="panel app-card">

        <p>Unesite podatke sportskog kluba koji isplaćuje honorare.</p>

        @if (session('success'))
            <div class="success success-icon" role="status">
                <i class="bi bi-check-circle" aria-hidden="true"></i>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="error validation-summary" role="alert">
                <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                Provjerite označena polja i ispravite unesene podatke.
            </div>
        @endif

        <form action="{{ route('organization.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="field">
                <label for="name">Naziv organizacije *</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $organization?->name) }}"
                    maxlength="255"
                    required
                >
                @error('name')
                    <div class="error validation-error" role="alert"><i class="bi bi-exclamation-circle" aria-hidden="true"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="street">Ulica *</label>
                <input
                    type="text"
                    id="street"
                    name="street"
                    value="{{ old('street', $organization?->street) }}"
                    maxlength="255"
                    required
                >
                @error('street')
                    <div class="error validation-error" role="alert"><i class="bi bi-exclamation-circle" aria-hidden="true"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="house_number">Kućni broj *</label>
                <input type="text" id="house_number" name="house_number" value="{{ old('house_number', $organization?->house_number) }}" maxlength="30" required>
                @error('house_number')<div class="error validation-error" role="alert">{{ $message }}</div>@enderror
            </div>

            <div class="field">
                <label for="postal_code">Poštanski broj *</label>
                <input
                    type="text"
                    id="postal_code"
                    name="postal_code"
                    value="{{ old('postal_code', $organization?->postal_code) }}"
                    maxlength="10"
                    required
                >
                @error('postal_code')
                    <div class="error validation-error" role="alert"><i class="bi bi-exclamation-circle" aria-hidden="true"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="city">Mjesto *</label>
                <input
                    type="text"
                    id="city"
                    name="city"
                    value="{{ old('city', $organization?->city) }}"
                    maxlength="255"
                    required
                >
                @error('city')
                    <div class="error validation-error" role="alert"><i class="bi bi-exclamation-circle" aria-hidden="true"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="oib">OIB organizacije *</label>
                <input
                    type="text"
                    id="oib"
                    name="oib"
                    value="{{ old('oib', $organization?->oib) }}"
                    inputmode="numeric"
                    pattern="[0-9]{11}"
                    minlength="11"
                    maxlength="11"
                    required
                >
                <div class="help">Unesite točno 11 znamenki.</div>
                @error('oib')
                    <div class="error validation-error" role="alert"><i class="bi bi-exclamation-circle" aria-hidden="true"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="iban">IBAN organizacije *</label>
                <input
                    type="text"
                    id="iban"
                    name="iban"
                    value="{{ old('iban', $organization?->iban) }}"
                    maxlength="34"
                    required
                >
                <div class="help">Primjer hrvatskog IBAN-a: HR i 19 znamenki.</div>
                @error('iban')
                    <div class="error validation-error" role="alert"><i class="bi bi-exclamation-circle" aria-hidden="true"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="email">E-pošta organizacije *</label>
                <input type="email" id="email" name="email" value="{{ old('email', $organization?->email) }}" maxlength="255" required>
                @error('email')<div class="error validation-error" role="alert">{{ $message }}</div>@enderror
            </div>

            <div class="field">
                <label for="responsible_person_first_name">Odgovorna osoba – ime *</label>
                <input type="text" id="responsible_person_first_name" name="responsible_person_first_name" value="{{ old('responsible_person_first_name', $organization?->responsible_person_first_name) }}" maxlength="255" required>
                @error('responsible_person_first_name')<div class="error validation-error" role="alert">{{ $message }}</div>@enderror
            </div>

            <div class="field">
                <label for="responsible_person_last_name">Odgovorna osoba – prezime *</label>
                <input type="text" id="responsible_person_last_name" name="responsible_person_last_name" value="{{ old('responsible_person_last_name', $organization?->responsible_person_last_name) }}" maxlength="255" required>
                @error('responsible_person_last_name')<div class="error validation-error" role="alert">{{ $message }}</div>@enderror
            </div>

            <div class="actions">
                <button type="submit" class="button icon-action app-button"><i class="bi bi-check-circle" aria-hidden="true"></i> Spremi podatke</button>
                <a href="{{ url('/') }}" class="button secondary app-button"><i class="bi bi-arrow-left" aria-hidden="true"></i> Povratak na početnu</a>
            </div>
        </form>
    </div>
@endsection
