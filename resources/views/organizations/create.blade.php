@extends('layouts.app')
@section('title', 'Nova organizacija | Obračun drugog dohotka')
@section('body-class', 'view-style-1')
@push('page-styles')
<style>
/* Styles migrated from inline attributes */
    .view-style-1 { margin: 0; padding: 30px; background: #f1f5f9; font-family: Arial, sans-serif; color: #1e293b; }

    .view-style-3 { color: #17365c; }
    .view-style-4 { color: #64748b; margin-bottom: 24px; }
    .view-style-5 { margin-bottom: 20px; padding: 14px; background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; border-radius: 6px; }
    .view-style-6 { margin: 0; padding-left: 20px; }
    .view-style-7 { padding: 24px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; }
    .view-style-8 { margin-bottom: 18px; }
    .view-style-9 { display: block; margin-bottom: 6px; font-weight: 600; }
    .view-style-10 { box-sizing: border-box; width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 5px; }
    .view-style-11 { margin-bottom: 24px; }
    .view-style-12 { padding: 12px 18px; background: #17365c; color: #ffffff; border: 0; border-radius: 6px; cursor: pointer; }
    .view-style-13 { display: inline-block; margin-left: 8px; padding: 12px 18px; background: #e2e8f0; color: #17365c; text-decoration: none; border-radius: 6px; }
</style>
@endpush

@section('content')
<div class="view-style-2">
        <h1 class="view-style-3 app-page-title"><i class="bi bi-building-add" aria-hidden="true"></i> Nova organizacija</h1>
        <p class="view-style-4">
            Unesite podatke nove organizacije. Postojeće organizacije ostaju sačuvane.
        </p>

        @if ($errors->any())
            <div class="view-style-5 validation-summary" role="alert">
                <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                <ul class="view-style-6">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('organizations.store') }}"
              class="view-style-7">
            @csrf

            <div class="view-style-8">
                <label for="name" class="view-style-9"><i class="bi bi-building" aria-hidden="true"></i> Naziv organizacije *</label>
                <input id="name" name="name" value="{{ old('name') }}" required
                       class="view-style-10">
            </div>

            <div class="view-style-8">
                <label for="street" class="view-style-9">Ulica *</label>
                <input id="street" name="street" value="{{ old('street') }}" required
                       class="view-style-10">
            </div>

            <div class="view-style-8">
                <label for="house_number" class="view-style-9">Kućni broj *</label>
                <input id="house_number" name="house_number" value="{{ old('house_number') }}" required class="view-style-10">
            </div>

            <div class="view-style-8">
                <label for="postal_code" class="view-style-9">Poštanski broj *</label>
                <input id="postal_code" name="postal_code" value="{{ old('postal_code') }}" required
                       class="view-style-10">
            </div>

            <div class="view-style-8">
                <label for="city" class="view-style-9">Mjesto *</label>
                <input id="city" name="city" value="{{ old('city') }}" required
                       class="view-style-10">
            </div>

            <div class="view-style-8">
                <label for="oib" class="view-style-9">OIB organizacije *</label>
                <input id="oib" name="oib" value="{{ old('oib') }}" required maxlength="11"
                       inputmode="numeric" pattern="[0-9]{11}"
                       class="view-style-10">
            </div>

            <div class="view-style-11">
                <label for="iban" class="view-style-9"><i class="bi bi-bank" aria-hidden="true"></i> IBAN organizacije *</label>
                <input id="iban" name="iban" value="{{ old('iban') }}" required maxlength="34"
                       class="view-style-10">
            </div>

            <div class="view-style-8">
                <label for="email" class="view-style-9">E-pošta organizacije *</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required class="view-style-10">
            </div>
            <div class="view-style-8">
                <label for="responsible_person_first_name" class="view-style-9">Odgovorna osoba – ime *</label>
                <input id="responsible_person_first_name" name="responsible_person_first_name" value="{{ old('responsible_person_first_name') }}" required class="view-style-10">
            </div>
            <div class="view-style-8">
                <label for="responsible_person_last_name" class="view-style-9">Odgovorna osoba – prezime *</label>
                <input id="responsible_person_last_name" name="responsible_person_last_name" value="{{ old('responsible_person_last_name') }}" required class="view-style-10">
            </div>

            <button type="submit"
                    class="view-style-12 app-button">
                <i class="bi bi-check-circle" aria-hidden="true"></i> Spremi novu organizaciju
            </button>

            <a href="{{ route('organizations.index') }}"
               class="view-style-13 app-button">
                <i class="bi bi-arrow-left" aria-hidden="true"></i> Odustani
            </a>
        </form>
    </div>
@endsection
