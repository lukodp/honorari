@extends('layouts.app')
@section('title', 'Novi obračun | Obračun drugog dohotka')
@push('page-styles')
<style>


        .page-header {
            margin-bottom: 24px;
        }

        .page-header h1 {
            margin: 0 0 8px;
            color: #17365c;
            font-size: 28px;
            font-weight: 700;
        }

        .page-description {
            margin: 0;
            color: #64748b;
        }

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

        .form-card {
            padding: 28px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(23, 54, 92, 0.06);
        }

        .form-card h2 {
            margin: 0 0 24px;
            color: #17365c;
            font-size: 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
        }

        .form-group {
            min-width: 0;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 7px;
            color: #334155;
            font-weight: 600;
        }

        input,
        select {
            display: block;
            width: 100%;
            min-height: 44px;
            padding: 10px 12px;
            font-family: inherit;
            font-size: 15px;
            color: #263445;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            outline: none;
        }

        input:focus,
        select:focus {
            border-color: #174a7e;
            box-shadow: 0 0 0 3px rgba(23, 74, 126, 0.12);
        }

        .field-help {
            margin: 7px 0 0;
            color: #64748b;
            font-size: 13px;
        }

        .error-box {
            margin-bottom: 22px;
            padding: 14px 18px;
            color: #991b1b;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
        }

        .error-box ul {
            margin: 0;
            padding-left: 20px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 28px;
            padding-top: 22px;
            border-top: 1px solid #e2e8f0;
        }

        .primary-button {
            display: inline-block;
            min-height: 44px;
            padding: 11px 22px;
            color: #ffffff;
            background: #17365c;
            border: none;
            border-radius: 7px;
            font-family: inherit;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .primary-button:hover {
            background: #24517f;
        }

        @media (max-width: 640px) {


            .page-header h1 {
                font-size: 24px;
            }

            .form-card {
                padding: 20px 16px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 18px;
            }

            .form-group.full-width {
                grid-column: auto;
            }

            .form-actions {
                justify-content: stretch;
            }

            .primary-button {
                width: 100%;
            }
        }


    /* Styles migrated from inline attributes */
    .view-style-1 { margin-bottom: 22px; padding: 14px 18px; color: #166534; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; }
</style>
@endpush

@section('content')
<a class="back-link" href="{{ url('/') }}">
            <i class="bi bi-arrow-left" aria-hidden="true"></i> Povratak na početnu stranicu
        </a>

        <header class="page-header">
            <h1 class="app-page-title"><i class="bi bi-calculator" aria-hidden="true"></i> Novi obračun</h1>
            <p class="page-description">
                Obračun drugog dohotka za sportske suce.
            </p>
        </header>

        <section class="form-card app-card">
            <h2><i class="bi bi-cash-coin" aria-hidden="true"></i> Podaci o isplati</h2>

            @if ($errors->any())
                <div class="error-box validation-summary" role="alert">
                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if (session('success'))
                <div class="view-style-1 success-icon" role="status">
                    <i class="bi bi-check-circle" aria-hidden="true"></i>
                {{ session('success') }}
            </div>
            @endif
            <form
                action="{{ route('calculations.preview') }}"
                method="POST"
            >
                @csrf

                <div class="form-grid">

                    <div class="form-group full-width">
                        <label for="referee_id">
                            Sudac / primatelj
                        </label>

                        <select
                            name="referee_id"
                            id="referee_id"
                            required
                        >
                            <option value="">
                                -- Odaberite primatelja --
                            </option>

                            @foreach ($referees as $referee)
                                <option
                                    value="{{ $referee->id }}"
                                    {{ old('referee_id') == $referee->id ? 'selected' : '' }}
                                >
                                    {{ $referee->last_name }}
                                    {{ $referee->first_name }}
                                    — {{ $referee->city }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="payment_date">
                            <i class="bi bi-calendar3" aria-hidden="true"></i> Datum isplate
                        </label>

                        <input
                            type="date"
                            name="payment_date"
                            id="payment_date"
                            min="{{ date('Y-m-d') }}"
                            value="{{ old('payment_date', date('Y-m-d')) }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="net_amount">
                            <i class="bi bi-currency-euro" aria-hidden="true"></i> Neto primitak (€)
                        </label>

                        <input
                            type="number"
                            name="net_amount"
                            id="net_amount"
                            min="0.01"
                            step="0.01"
                            value="{{ old('net_amount') }}"
                            placeholder="Npr. 50,00"
                            required
                        >

                        <p class="field-help">
                            Unesite neto primitak, odnosno iznos koji
                            sudac treba primiti na svoj račun.
                        </p>
                    </div>

                </div>

                <div class="form-actions">
                    <button type="submit" class="primary-button app-button">
                        <i class="bi bi-eye" aria-hidden="true"></i> Provjeri poreznu stopu
                    </button>
                </div>

            </form>
        </section>
@endsection
