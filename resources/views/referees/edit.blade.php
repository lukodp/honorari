@extends('layouts.app')
@section('title', 'Uređivanje suca - Obračun drugog dohotka')
@push('page-styles')
<style>


        h1 {
            margin-bottom: 8px;
        }

        .subtitle {
            color: #64748b;
            margin-bottom: 25px;
        }

        .panel {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.05);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        label {
            font-weight: bold;
            font-size: 14px;
        }

        input[type="text"] {
            padding: 11px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 15px;
            width: 100%;
            box-sizing: border-box;
        }

        .status {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 22px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 6px;
        }

        .status label {
            font-weight: normal;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 25px;
            flex-wrap: wrap;
        }

        .button {
            display: inline-block;
            padding: 12px 18px;
            background: #174a7e;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }

        .button-secondary {
            background: #64748b;
        }

        .errors {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px 18px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        @media (max-width: 600px) {


            .form-grid {
                grid-template-columns: 1fr;
            }

            .full-width {
                grid-column: auto;
            }
        }
</style>
@endpush

@section('content')
<div class="container">

    <h1 class="app-page-title"><i class="bi bi-person-fill-gear" aria-hidden="true"></i> Uređivanje suca</h1>
    <p class="subtitle">
        Izmijenite podatke ili promijenite status suca.
    </p>

    <div class="panel app-card">

        @if ($errors->any())
            <div class="errors validation-summary" role="alert">
                <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                <strong>Provjerite unesene podatke:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ route('primatelji.update', $referee) }}">
            @csrf
            @method('PUT')

            <div class="form-grid">

                <div class="field">
                    <label for="first_name">Ime</label>
                    <input
                        type="text"
                        id="first_name"
                        name="first_name"
                        value="{{ old('first_name', $referee->first_name) }}"
                        required
                    >
                </div>

                <div class="field">
                    <label for="last_name">Prezime</label>
                    <input
                        type="text"
                        id="last_name"
                        name="last_name"
                        value="{{ old('last_name', $referee->last_name) }}"
                        required
                    >
                </div>

                <div class="field full-width">
                    <label for="oib">OIB</label>
                    <input
                        type="text"
                        id="oib"
                        name="oib"
                        value="{{ old('oib', $referee->oib) }}"
                        inputmode="numeric"
                        maxlength="11"
                        pattern="[0-9]{11}"
                        required
                    >
                </div>

                <div class="field full-width">
                    <label for="address">Ulica i kućni broj</label>
                    <input
                        type="text"
                        id="address"
                        name="address"
                        value="{{ old('address', $referee->address) }}"
                        required
                    >
                </div>

                <div class="field">
                    <label for="postal_code">Poštanski broj</label>
                    <input
                        type="text"
                        id="postal_code"
                        name="postal_code"
                        value="{{ old('postal_code', $referee->postal_code) }}"
                        maxlength="10"
                        required
                    >
                </div>

                <div class="field">
                    <label for="local_government_id"><i class="bi bi-geo-alt" aria-hidden="true"></i> Grad ili općina</label>
                    <select
                        id="local_government_id"
                        name="local_government_id"
                        required
                    >
                        <option value="">-- Odaberite grad ili općinu --</option>

                        @foreach ($localGovernments as $localGovernment)
                            <option
                                value="{{ $localGovernment->id }}"
                                @selected(
                                    old(
                                        'local_government_id',
                                        $referee->local_government_id
                                    ) == $localGovernment->id
                                )
                            >
                                {{ $localGovernment->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field full-width">
                    <label for="iban"><i class="bi bi-bank" aria-hidden="true"></i> IBAN</label>
                    <input
                        type="text"
                        id="iban"
                        name="iban"
                        value="{{ old('iban', $referee->iban) }}"
                        maxlength="34"
                        required
                    >
                </div>

            </div>

            <div class="status">
                <input
                    type="hidden"
                    name="is_active"
                    value="0"
                >

                <input
                    type="checkbox"
                    id="is_active"
                    name="is_active"
                    value="1"
                    @checked(old('is_active', $referee->is_active))
                >

                <label for="is_active">
                    Sudac je aktivan i može se koristiti u novim obračunima
                </label>
            </div>

            <div class="actions">
                <button type="submit" class="button app-button">
                    <i class="bi bi-check-circle" aria-hidden="true"></i> Spremi promjene
                </button>

                <a href="{{ route('primatelji.index') }}"
                   class="button button-secondary app-button">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i> Odustani
                </a>
            </div>
        </form>

    </div>
</div>
@endsection
