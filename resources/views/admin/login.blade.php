<x-admin-layout title="Prijava">
    <div class="card login">
        <h1>Zlatna karta</h1>
        <p class="muted">Prijava u administraciju</p>

        <form method="POST" action="{{ route('admin.login') }}">
            @csrf
            <x-field name="email" label="Email" type="email" required autofocus />
            <x-field name="password" label="Lozinka" type="password" required />
            <button class="btn primary">Prijavi se</button>
        </form>
    </div>
</x-admin-layout>
