@extends('layouts.app')
@section('title', 'Organizacije | Obračun drugog dohotka')
@section('body-class', 'view-style-1')
@push('page-styles')
<style>
/* Styles migrated from inline attributes */
    .view-style-1 { margin: 0; padding: 30px; background: #f1f5f9; font-family: Arial, sans-serif; color: #1e293b; }

    .view-style-3 { color: #17365c; margin-bottom: 8px; }
    .view-style-4 { color: #64748b; margin-bottom: 28px; }
    .view-style-5 { margin-bottom: 20px; padding: 14px 18px; color: #166534; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; }
    .view-style-6 { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; }
    .view-style-7 { padding: 20px; border-bottom: 1px solid #e2e8f0; }
    .view-style-8 { display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap; }
    .view-style-9 { font-size: 17px; font-weight: 600; color: #17365c; }
    .view-style-10 { margin-top: 6px; color: #64748b; font-size: 14px; }
    .view-style-11 { margin-top: 4px; color: #64748b; font-size: 14px; }
    .view-style-12 { display: inline-block; padding: 8px 12px; background: #dcfce7; color: #166534; border-radius: 6px; font-size: 13px; font-weight: 600; }
    .view-style-13 { display: inline-block; padding: 8px 12px; background: #e2e8f0; color: #475569; border-radius: 6px; font-size: 13px; }
    .view-style-14 { margin-top: 10px; }
    .view-style-15 { padding: 9px 12px; background: #17365c; color: #ffffff; border: 0; border-radius: 6px; cursor: pointer; }
    .view-style-16 { padding: 9px 12px; background: #b91c1c; color: #ffffff; border: 0; border-radius: 6px; cursor: pointer; }
    .view-style-17 { padding: 24px; }
    .view-style-18 { margin-top: 24px; }
    .view-style-19 { display: inline-block; margin-right: 8px; padding: 12px 18px; background: #15803d; color: #ffffff; text-decoration: none; border-radius: 6px; }
    .view-style-20 { display: inline-block; padding: 12px 18px; background: #17365c; color: #ffffff; text-decoration: none; border-radius: 6px; }
    .view-style-21 { display: inline-block; margin-left: 8px; padding: 12px 18px; background: #ffffff; color: #17365c; text-decoration: none; border: 1px solid #cbd5e1; border-radius: 6px; }
</style>
@endpush

@section('content')
<div class="view-style-2">
        <h1 class="view-style-3 app-page-title"><i class="bi bi-buildings" aria-hidden="true"></i> Organizacije</h1>

        <p class="view-style-4">
            Pregled svih organizacija i odabir organizacije za nove obračune.
        </p>
        @if (session('success'))
            <div class="view-style-5 success-icon" role="status">
                <i class="bi bi-check-circle" aria-hidden="true"></i>
                {{ session('success') }}
            </div>
        @endif
        <div class="view-style-6 app-card app-card--flush">
            @forelse ($organizations as $organization)
                <div class="view-style-7">
                    <div class="view-style-8">
                        <div>
                            <div class="view-style-9">
                                {{ $organization->name }}
                            </div>

                            <div class="view-style-10">
                                {{ $organization->street }} {{ $organization->house_number }},
                                {{ $organization->postal_code }} {{ $organization->city }}
                            </div>

                            <div class="view-style-11">
                                OIB: {{ $organization->oib }}
                            </div>
                        </div>

                        <div>                           
                            @if ($organization->is_active)
                                <span class="view-style-12">
                                    <i class="bi bi-check-circle" aria-hidden="true"></i> Aktivna organizacija
                                </span>
                            @else
                                <span class="view-style-13">
                                    <i class="bi bi-exclamation-circle" aria-hidden="true"></i> Nije aktivna
                                </span>

                                <form method="POST" action="{{ route('organizations.activate', $organization) }}" class="view-style-14">
                                    @csrf
                                    <button type="submit"
                                            class="view-style-15 app-button">
                                        <i class="bi bi-check-circle" aria-hidden="true"></i> Postavi kao aktivnu
                                    </button>
                                </form>
                            @endif

                            <form method="POST"
                                  action="{{ route('organizations.destroy', $organization) }}"
                                  class="view-style-14"
                                  onsubmit="return confirm('Trajno obrisati ovu organizaciju? Spremljeni obračuni ostat će sačuvani.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="view-style-16 app-button">
                                    <i class="bi bi-trash" aria-hidden="true"></i> Obriši organizaciju
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="view-style-17">
                    Još nema unesenih organizacija.
                </div>
            @endforelse
        </div>

        <div class="view-style-18">
            <a href="{{ route('organizations.create') }}"
            class="view-style-19 app-button">
                <i class="bi bi-plus-circle" aria-hidden="true"></i> Dodaj novu organizaciju
            </a>
            <a href="{{ route('organization.edit') }}"
               class="view-style-20 app-button">
                <i class="bi bi-pencil" aria-hidden="true"></i> Uredi podatke postojeće organizacije
            </a>

            <a href="{{ url('/') }}"
               class="view-style-21 app-button">
                <i class="bi bi-arrow-left" aria-hidden="true"></i> Povratak na početnu
            </a>
        </div>
    </div>
@endsection
