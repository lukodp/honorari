@extends('layouts.app')
@section('title', 'Nova porezna stopa - Obračun drugog dohotka')
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

        input[type="text"],
        input[type="number"],
        input[type="date"] {
            padding: 11px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 15px;
            width: 100%;
            box-sizing: border-box;
        }

        .help {
            color: #64748b;
            font-size: 12px;
            line-height: 1.5;
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

    <h1 class="app-page-title"><i class="bi bi-percent" aria-hidden="true"></i> Nova porezna stopa</h1>

    <p class="subtitle">
        Unesite stopu i pripadajući koeficijent za određeno razdoblje primjene.
    </p>

    <div class="panel app-card">

        @if (session('success'))
            <div class="success-icon" role="status">{{ session('success') }}</div>
        @endif

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

        <form method="POST" action="{{ route('porezne-stope.store') }}">
            @csrf

            <div class="form-grid">

                <div class="field full-width">
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
                                @selected(old('local_government_id') == $localGovernment->id)
                            >
                                {{ $localGovernment->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field full-width">
                    <label for="iban"><i class="bi bi-bank" aria-hidden="true"></i> IBAN računa grada ili općine</label>
                    <input
                        type="text"
                        id="iban"
                        name="iban"
                        value="{{ old('iban') }}"
                        maxlength="34"
                        placeholder="Npr. HR..."
                        autocomplete="off"
                        required
                    >
                    <span class="help">
                        Unesite IBAN računa za uplatu poreza koji vrijedi u navedenom razdoblju.
                    </span>
                </div>

                <div class="field">
                    <label for="rate"><i class="bi bi-percent" aria-hidden="true"></i> Porezna stopa (%)</label>
                    <input
                        type="number"
                        id="rate"
                        name="rate"
                        value="{{ old('rate') }}"
                        min="0"
                        max="100"
                        step="0.01"
                        placeholder="Npr. 20.00"
                        required
                    >
                    <span class="help">
                        Unosi se postotak, npr. 20.00 za stopu od 20 %.
                    </span>
                </div>

                <div class="field">
                    <label for="coefficient">Koeficijent – Varijanta 2</label>
                    <input
                        type="text"
                        id="coefficient"
                        value=""
                        placeholder="Automatski se određuje"
                        readonly
                    >
                    <span class="help">
                        Koeficijent se automatski određuje prema odabranoj poreznoj stopi.
                    </span>
                </div>

                <div class="field">
                    <label for="valid_from"><i class="bi bi-calendar3" aria-hidden="true"></i> Vrijedi od</label>
                    <input
                        type="date"
                        id="valid_from"
                        name="valid_from"
                        value="{{ old('valid_from') }}"
                        required
                    >
                </div>

                <div class="field">
                    <label for="valid_until"><i class="bi bi-calendar3" aria-hidden="true"></i> Vrijedi do (nije obavezno)</label>
                    <input
                        type="date"
                        id="valid_until"
                        name="valid_until"
                        value="{{ old('valid_until') }}"
                    >
                    <span class="help">
                        Ostavite prazno ako završni datum nije određen.
                    </span>
                </div>

            </div>

            <div class="actions">
                <button type="submit" class="button app-button">
                    <i class="bi bi-check-circle" aria-hidden="true"></i> Spremi poreznu stopu
                </button>

                <a href="{{ route('porezne-stope.index') }}"
                   class="button button-secondary app-button">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i> Odustani
                </a>
            </div>
        </form>

        <hr style="margin: 28px 0;">
        <h2>Dodaj općinu ili grad</h2>
        <p class="help">Šifra se pohranjuje izvorno. Za JOPPD se prikazuje s vodećim nulama do pet znamenki.</p>
        <form method="POST" action="{{ route('local-governments.store') }}">
            @csrf
            <div class="form-grid">
                <div class="field">
                    <label for="new_local_government_code">Šifra (2–5 znamenki)</label>
                    <input type="text" id="new_local_government_code" name="code" inputmode="numeric" pattern="[0-9]{2,5}" maxlength="5" value="{{ old('code') }}" required>
                    @error('code')<span class="help" role="alert">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="new_local_government_name">Naziv općine ili grada</label>
                    <input type="text" id="new_local_government_name" name="name" maxlength="255" value="{{ old('name') }}" required>
                    @error('name')<span class="help" role="alert">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="actions">
                <button type="submit" class="button app-button">Dodaj lokalnu jedinicu</button>
            </div>
        </form>

    </div>
</div>
<script>
    const coefficients = {
        '15.00': '1.176471',
        '17.00': '1.204819',
        '17.50': '1.212121',
        '18.00': '1.219512',
        '20.00': '1.250000',
        '21.00': '1.265823',
        '21.30': '1.270648',
        '21.40': '1.272265',
        '21.50': '1.273885',
        '21.60': '1.275510',
        '21.80': '1.278772',
        '22.00': '1.282051',
        '22.20': '1.285347',
        '22.30': '1.287001',
        '22.40': '1.288660',
        '23.00': '1.298701',
        '23.60': '1.308901'
    };

    const rateInput = document.getElementById('rate');
    const coefficientInput = document.getElementById('coefficient');

    function updateCoefficient() {
        const rate = Number(rateInput.value);

        if (rateInput.value === '' || !Number.isFinite(rate)) {
            coefficientInput.value = '';
            return;
        }

        const key = rate.toFixed(2);
        coefficientInput.value = coefficients[key] ?? '';
    }

    rateInput.addEventListener('input', updateCoefficient);
    updateCoefficient();
</script>
@endsection
