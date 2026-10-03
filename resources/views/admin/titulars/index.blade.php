<x-admin-layout title="Titulari">
    <div class="head">
        <div>
            <h1>Titulari</h1>
            <p class="muted">Tvrtke koje daju popuste. Upišeš ih jednom i koristiš u svakom izdanju.</p>
        </div>
        <a class="btn primary" href="{{ route('admin.titulars.create') }}">+ Novi titular</a>
    </div>

    <form method="GET" class="row" style="margin-bottom:16px; max-width:480px">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Traži po imenu...">
        <button class="btn">Traži</button>
    </form>

    <div class="table-wrap">
        <table>
            <tr><th></th><th>Naziv</th><th>Lokacije</th><th>Kuponi</th><th></th></tr>
            @forelse ($titulars as $titular)
                <tr>
                    <td>
                        @if ($titular->logo_path)
                            <img class="thumb" src="{{ Storage::disk('public')->url($titular->logo_path) }}" alt="">
                        @else
                            <span class="thumb"></span>
                        @endif
                    </td>
                    <td><a href="{{ route('admin.titulars.edit', $titular) }}"><strong>{{ $titular->name }}</strong></a></td>
                    <td>{{ $titular->locations_count }}</td>
                    <td>{{ $titular->coupons_count }}</td>
                    <td><a href="{{ route('admin.titulars.edit', $titular) }}">Uredi</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">Nema titulara.</td></tr>
            @endforelse
        </table>
    </div>
</x-admin-layout>
