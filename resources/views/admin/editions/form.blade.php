<x-admin-layout :title="$edition->exists ? 'Uredi izdanje' : 'Novo izdanje'">
    <div class="head">
        <h1>{{ $edition->exists ? 'Uredi izdanje' : 'Novo izdanje' }}</h1>
        @if ($edition->exists)
            <a class="btn" href="{{ route('admin.editions.show', $edition) }}">Natrag</a>
        @endif
    </div>

    <form method="POST" enctype="multipart/form-data"
          action="{{ $edition->exists ? route('admin.editions.update', $edition) : route('admin.editions.store') }}">
        @csrf
        @if ($edition->exists) @method('PUT') @endif
        <p class="required-note"><b>*</b> obavezno polje</p>

        <div class="card">
            <x-field name="name" label="Naziv izdanja" :value="$edition->name" required
                     help="Npr. Zima 2026/27. Studenti ovo ne vide, služi samo tebi." />

            @unless ($edition->exists)
                <div class="field">
                    <label for="copy_from">Kopiraj kupone iz izdanja</label>
                    <select id="copy_from" name="copy_from">
                        <option value="">— ne kopiraj, krećem od nule —</option>
                        @foreach ($editions as $other)
                            <option value="{{ $other->id }}" @selected(old('copy_from', $other->isActive() ? $other->id : null) == $other->id)>
                                {{ $other->name }} ({{ $other->statusLabel() }})
                            </option>
                        @endforeach
                    </select>
                    <small>Uštedi vrijeme: svi kuponi se kopiraju, a ti samo promijeniš što treba.</small>
                </div>
            @endunless
        </div>

        <div class="card">
            <h2 style="margin-top:0">Tekst "O projektu"</h2>
            <x-field name="about_title" label="Naslov" :value="$edition->about_title" placeholder="O projektu"
                     help="Veliki naslov na početnoj. Kratko, npr. O projektu." />
            <x-field name="about_body" label="Tekst" type="textarea" :value="$edition->about_body" rows="12"
                     help="Prazan red = novi odlomak. **ovako** = podebljano." />
        </div>

        <div class="card">
            <h2 style="margin-top:0">Tiskano izdanje</h2>
            <div class="grid-2">
                <x-upload name="pdf" label="PDF" kind="pdf" :current="$edition->pdf_path"
                          help="Novi PDF zamijeni stari." />
                <x-upload name="cover" label="Naslovnica (slika)" :current="$edition->cover_path"
                          help="Slika naslovnice za popis tiskanih izdanja." />
            </div>
        </div>

        <button class="btn primary">Spremi</button>
    </form>
</x-admin-layout>
