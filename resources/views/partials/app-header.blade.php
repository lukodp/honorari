<header class="app-header">
    <div class="app-header-main">
        <div class="app-header-content">
            <a class="app-brand" href="{{ url('/') }}" aria-label="Početna stranica">
                <img class="app-brand-logo" src="{{ asset('images/logo-obracun-drugog-dohotka.png') }}" alt="">
                <span class="app-brand-copy">
                    <span class="app-brand-title">Obračun sudačkih honorara</span>
                    <span class="app-brand-subtitle">Lokalna aplikacija za obračun sudačkih honorara i pripremu naloga za plaćanje</span>
                </span>
            </a>

            @if ($activeOrganization)
                <section class="app-organization-card" aria-label="Trenutna organizacija">
                    <span class="app-organization-label">Trenutna organizacija</span>
                    <strong class="app-organization-name">{{ $activeOrganization->name }}</strong>
                    <a class="app-organization-action" href="{{ route('organizations.index') }}">
                        <i class="bi bi-bank" aria-hidden="true"></i>
                        Promijeni organizaciju
                    </a>
                </section>
            @elseif (!$hasOrganizations)
                <section class="app-organization-notice" aria-label="Organizacija nije unesena">
                    <span class="app-organization-label">Potrebno unijeti organizaciju</span>
                    <a class="app-organization-action" href="{{ route('organizations.create') }}">
                        <i class="bi bi-plus-circle" aria-hidden="true"></i>
                        Unesi novu organizaciju
                    </a>
                </section>
            @else
                <section class="app-organization-notice" aria-label="Aktivna organizacija nije odabrana">
                    <span class="app-organization-label">Nije odabrana aktivna organizacija</span>
                    <a class="app-organization-action" href="{{ route('organizations.index') }}">
                        <i class="bi bi-building-check" aria-hidden="true"></i>
                        Odaberi aktivnu organizaciju
                    </a>
                </section>
            @endif
        </div>
    </div>

    <nav class="app-navigation" aria-label="Glavna navigacija">
        <div class="app-navigation-links">
            <a class="app-navigation-link {{ request()->path() === '/' ? 'is-active' : '' }}"
               href="{{ url('/') }}" @if (request()->path() === '/') aria-current="page" @endif>
                <i class="bi bi-house-door" aria-hidden="true"></i>
                Početna
            </a>
            <a class="app-navigation-link {{ request()->routeIs('calculations.create', 'calculations.preview') ? 'is-active' : '' }}"
               href="{{ route('calculations.create') }}" @if (request()->routeIs('calculations.create', 'calculations.preview')) aria-current="page" @endif>
                <i class="bi bi-calculator" aria-hidden="true"></i>
                Novi obračun
            </a>
            <a class="app-navigation-link {{ request()->routeIs('primatelji.*') ? 'is-active' : '' }}"
               href="{{ route('primatelji.index') }}" @if (request()->routeIs('primatelji.*')) aria-current="page" @endif>
                <i class="bi bi-people" aria-hidden="true"></i>
                Primatelji
            </a>
            <a class="app-navigation-link {{ request()->routeIs('calculations.index', 'calculations.show', 'calculations.payment-orders') ? 'is-active' : '' }}"
               href="{{ route('calculations.index') }}" @if (request()->routeIs('calculations.index', 'calculations.show', 'calculations.payment-orders')) aria-current="page" @endif>
                <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
                Obračuni
            </a>
            <a class="app-navigation-link {{ request()->routeIs('porezne-stope.*') ? 'is-active' : '' }}"
               href="{{ route('porezne-stope.index') }}" @if (request()->routeIs('porezne-stope.*')) aria-current="page" @endif>
                <i class="bi bi-percent" aria-hidden="true"></i>
                Porezne stope
            </a>
        </div>
    </nav>
</header>
