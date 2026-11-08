@extends('layouts.app')
@section('title', 'Obračun sudačkih honorara')
@push('page-styles')
<style>
.welcome {
    margin-bottom: 28px;
}

.welcome-eyebrow {
    margin: 0 0 8px;
    color: #3974b6;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.welcome-description {
    margin: 0;
    color: #405b7d;
    font-size: 16px;
    line-height: 1.5;
}

.menu {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 22px;
}

.menu-item {
    position: relative;
    display: flex;
    min-height: 216px;
    flex-direction: column;
    align-items: flex-start;
    padding: 18px 22px 22px;
    color: inherit;
    text-decoration: none;
    background: rgba(255, 255, 255, 0.96);
    border: 1px solid #d8e4f2;
    border-radius: 11px;
    box-shadow: 0 5px 16px rgba(25, 59, 98, 0.06);
    transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
}

.menu-item:hover,
.menu-item:focus-visible {
    transform: translateY(-3px);
    border-color: #9abce1;
    box-shadow: 0 12px 24px rgba(25, 59, 98, 0.12);
    outline: none;
}

.menu-item-top {
    display: flex;
    width: 100%;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}

.menu-icon {
    display: block;
    width: 60px;
    height: 60px;
    object-fit: contain;
}

.menu-arrow {
    display: grid;
    width: 40px;
    height: 40px;
    color: #174a7e;
    font-size: 25px;
    font-weight: 700;
    line-height: 1;
    place-items: center;
    background: #e6f0fc;
    border-radius: 50%;
    transition: transform 160ms ease;
}

.menu-item:hover .menu-arrow,
.menu-item:focus-visible .menu-arrow {
    transform: translateX(3px);
}

.menu-item h3 {
    margin: 0 0 7px;
    color: #102b50;
    font-size: 19px;
    line-height: 1.25;
}

.menu-item p {
    margin: 0;
    color: #405b7d;
    font-size: 15px;
    line-height: 1.5;
}

@media (max-width: 900px) {
    .menu {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 620px) {
    .menu {
        grid-template-columns: 1fr;
        gap: 14px;
    }

    .menu-item {
        min-height: 0;
        padding: 17px 19px 20px;
    }
}
</style>
@endpush

@section('content')
<section class="welcome">
        <p class="welcome-eyebrow">Dobro došli</p>
        <h2 class="app-page-title">Početna stranica</h2>
        <p class="welcome-description">Odaberite funkcionalnost koju želite koristiti.</p>
    </section>

    <section class="menu" aria-label="Funkcionalnosti">
        <a class="menu-item" href="{{ route('calculations.create') }}">
            <span class="menu-item-top">
                <img class="menu-icon" src="{{ asset('images/icon-novi-obracun.png') }}" alt="">
                <span class="menu-arrow" aria-hidden="true">→</span>
            </span>
            <h3>Novi obračun</h3>
            <p>Unos datuma isplate i neto honorara te izračun bruto iznosa i poreza.</p>
        </a>

        <a class="menu-item" href="{{ route('primatelji.index') }}">
            <span class="menu-item-top">
                <img class="menu-icon" src="{{ asset('images/icon-primatelji.png') }}" alt="">
                <span class="menu-arrow" aria-hidden="true">→</span>
            </span>
            <h3>Primatelji</h3>
            <p>Evidencija sportskih sudaca i njihovih podataka za isplatu.</p>
        </a>

        <a class="menu-item" href="{{ route('calculations.index') }}">
            <span class="menu-item-top">
                <img class="menu-icon" src="{{ asset('images/icon-obracuni.png') }}" alt="">
                <span class="menu-arrow" aria-hidden="true">→</span>
            </span>
            <h3>Obračuni</h3>
            <p>Pregled i pretraživanje spremljenih obračuna.</p>
        </a>

        <a class="menu-item" href="{{ route('porezne-stope.index') }}">
            <span class="menu-item-top">
                <img class="menu-icon" src="{{ asset('images/icon-porezne-stope.png') }}" alt="">
                <span class="menu-arrow" aria-hidden="true">→</span>
            </span>
            <h3>Porezne stope</h3>
            <p>Pregled i ažuriranje stopa prema gradu i razdoblju važenja.</p>
        </a>

        <a class="menu-item" href="{{ route('joppd.create') }}">
            <span class="menu-item-top">
                <span class="menu-icon" aria-hidden="true"><i class="bi bi-file-earmark-code" style="font-size: 48px; color: #174a7e;"></i></span>
                <span class="menu-arrow" aria-hidden="true">→</span>
            </span>
            <h3>Kreiraj JOPPD obrazac</h3>
            <p>Priprema obračuna za datum isplate i preuzimanje XML datoteke.</p>
        </a>
    </section>
@endsection
