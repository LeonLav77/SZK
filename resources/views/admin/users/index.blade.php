<x-admin-layout title="Korisnici">
    <div class="head">
        <div>
            <h1>Korisnici</h1>
            <p class="muted">Ukupno: {{ $users->total() }}</p>
        </div>
    </div>

    <form method="GET" class="row" style="margin-bottom:16px; max-width:480px">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Traži po imenu ili emailu...">
        <button class="btn">Traži</button>
    </form>

    <div class="table-wrap">
        <table>
            <tr><th>Ime</th><th>Email</th><th>Registriran</th><th>Korištenja</th><th></th></tr>
            @forelse ($users as $user)
                <tr>
                    <td>{{ $user->name }} @if ($user->is_admin) <span class="badge">admin</span> @endif</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->created_at->format('d.m.Y.') }}</td>
                    <td>{{ $user->redemptions_count }}</td>
                    <td><a href="{{ route('admin.users.edit', $user) }}">Uredi</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">Nema korisnika.</td></tr>
            @endforelse
        </table>
    </div>

    <div class="pagination">
        @if ($users->previousPageUrl()) <a class="btn" href="{{ $users->previousPageUrl() }}">← Prethodna</a> @endif
        @if ($users->nextPageUrl()) <a class="btn" href="{{ $users->nextPageUrl() }}">Sljedeća →</a> @endif
    </div>
</x-admin-layout>
