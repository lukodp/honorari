@extends('layouts.app')
@section('title', 'Primatelji - Obračun drugog dohotka')
@push('page-styles')
<style>


        h1 {
            margin-bottom: 8px;
        }

        .subtitle {
            color: #64748b;
            margin-bottom: 25px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .button {
            display: inline-block;
            padding: 11px 16px;
            background: #174a7e;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .button:hover {
            background: #10365d;
        }

        .button-secondary {
            background: #64748b;
        }

        .search-form {
            display: flex;
            gap: 8px;
        }

        input[type="text"] {
            padding: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            min-width: 240px;
        }

        .panel {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.05);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 13px 10px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: #f8fafc;
        }

        .edit-link {
            color: #174a7e;
            font-weight: bold;
            text-decoration: none;
        }

        .delete-button {
            background: none;
            border: none;
            padding: 0;
            color: #b91c1c;
            cursor: pointer;
            font: inherit;
            font-weight: bold;
        }

        .message {
            padding: 12px 15px;
            margin-bottom: 18px;
            background: #dcfce7;
            color: #166534;
            border-radius: 6px;
        }

        .empty {
            padding: 25px 0;
            text-align: center;
            color: #64748b;
        }

        .pagination {
            margin-top: 20px;
        }


    /* Styles migrated from inline attributes */
    .view-style-1 { display: inline-block; margin-left: 10px; }
</style>
@endpush

@section('content')
<div class="container">

    <h1 class="app-page-title"><i class="bi bi-people" aria-hidden="true"></i> Primatelji</h1>
    <p class="subtitle">Evidencija sudaca i njihovih podataka za isplatu honorara.</p>

    <div class="topbar">
        <a href="{{ url('/') }}" class="button button-secondary app-button">
            <i class="bi bi-arrow-left" aria-hidden="true"></i> Povratak na početnu
        </a>

        <a href="{{ route('primatelji.create') }}" class="button app-button">
            <i class="bi bi-plus-circle" aria-hidden="true"></i> Dodaj suca
        </a>
    </div>

    @if (session('success'))
        <div class="message success-icon" role="status">
            <i class="bi bi-check-circle" aria-hidden="true"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="panel app-card">

        <form method="GET" action="{{ route('primatelji.index') }}" class="search-form">
            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Pretraži ime, prezime ili grad"
            >

            <button type="submit" class="button icon-action app-button"><i class="bi bi-search" aria-hidden="true"></i> Traži</button>

            @if ($search)
                <a href="{{ route('primatelji.index') }}"
                   class="button button-secondary app-button"><i class="bi bi-x-circle" aria-hidden="true"></i> Poništi</a>
            @endif
        </form>

        <br>

        <table>
            <thead>
                <tr>
                    <th>Ime i prezime</th>
                    <th>Grad</th>
                    <th>IBAN</th>
                    <th>Status</th>
                    <th>Akcija</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($referees as $referee)
                    <tr>
                        <td>{{ $referee->first_name }} {{ $referee->last_name }}</td>
                        <td>{{ $referee->city }}</td>
                        <td>{{ $referee->iban }}</td>
                        <td>{{ $referee->is_active ? 'Aktivan' : 'Neaktivan' }}</td>
                        <td>
                            <a class="edit-link"
                               href="{{ route('primatelji.edit', $referee) }}">
                                <i class="bi bi-pencil" aria-hidden="true"></i> Uredi
                            </a>
                            <form method="POST"
                                  action="{{ route('primatelji.destroy', $referee) }}"
                                  class="view-style-1"
                                  onsubmit="return confirm('Želite li obrisati ovog suca? Povezani obračuni ostat će spremljeni.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="delete-button app-button">
                                    <i class="bi bi-trash" aria-hidden="true"></i> Obriši
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty">
                            Nema evidentiranih sudaca.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination">
            {{ $referees->links() }}
        </div>

    </div>
</div>
@endsection
