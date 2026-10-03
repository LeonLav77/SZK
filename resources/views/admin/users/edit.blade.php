<x-admin-layout title="Uredi korisnika">
    <div class="head">
        <h1>{{ $user->name }}</h1>
        <a class="btn" href="{{ route('admin.users.index') }}">Natrag</a>
    </div>

    <form class="card" method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf @method('PUT')
        <x-field name="name" label="Ime" :value="$user->name" required />
        <x-field name="email" label="Email" type="email" :value="$user->email" required />
        <x-field name="password" label="Nova lozinka" type="password" autocomplete="new-password"
                 help="Ako je student zaboravio lozinku, upiši novu i javi mu je. Ostavi prazno da se ne mijenja." />
        <button class="btn primary">Spremi</button>
    </form>

    @unless ($user->is(auth()->user()))
        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" style="margin-top:40px"
              onsubmit="return confirm('Sigurno obrisati ovog korisnika?')">
            @csrf @method('DELETE')
            <button class="link danger">Obriši korisnika</button>
        </form>
    @endunless
</x-admin-layout>
