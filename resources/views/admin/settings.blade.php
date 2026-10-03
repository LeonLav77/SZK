<x-admin-layout title="Postavke">
    <div class="head">
        <h1>Postavke</h1>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf @method('PUT')

        <div class="card">
            <h2 style="margin-top:0">Kontakt</h2>
            <p class="muted">Prikazuje se u kontakt kućici na početnoj stranici.</p>
            <x-field name="contact_email" label="Email" type="email" :value="$settings['contact_email']" />
            <x-field name="contact_instagram" label="Instagram" :value="$settings['contact_instagram']" placeholder="@studentska.zlatna.karta" />
            <x-field name="contact_link" label="Dodatni link" type="url" :value="$settings['contact_link']" placeholder="https://..." />
        </div>

        <div class="card">
            <h2 style="margin-top:0">Kupon</h2>
            <x-field name="countdown_seconds" label="Odbrojavanje (sekunde)" type="number" min="10" max="600"
                     :value="$settings['countdown_seconds']" style="max-width:160px"
                     help="Koliko dugo kupon stoji na ekranu nakon što student klikne Iskoristi kupon." />
        </div>

        <button class="btn primary">Spremi postavke</button>
    </form>
</x-admin-layout>
