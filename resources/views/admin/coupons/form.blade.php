<x-admin-layout :title="$coupon->exists ? 'Uredi kupon' : 'Novi kupon'">
    <div class="head">
        <div>
            <h1>{{ $coupon->exists ? 'Uredi kupon' : 'Novi kupon' }}</h1>
            <p class="muted">Izdanje: {{ $edition->name }}</p>
        </div>
        <a class="btn" href="{{ route('admin.editions.show', $edition) }}">Natrag</a>
    </div>

    <form method="POST" enctype="multipart/form-data"
          action="{{ $coupon->exists ? route('admin.coupons.update', $coupon) : route('admin.editions.coupons.store', $edition) }}">
        @csrf
        @if ($coupon->exists) @method('PUT') @endif

        <div class="card">
            <div class="grid-2">
                <div class="field">
                    <label for="titular_id">Titular (tvrtka)</label>
                    <select id="titular_id" name="titular_id" required>
                        <option value="">— odaberi —</option>
                        @foreach ($titulars as $titular)
                            <option value="{{ $titular->id }}" @selected(old('titular_id', $coupon->titular_id) == $titular->id)>{{ $titular->name }}</option>
                        @endforeach
                    </select>
                    <small>Nema ga na popisu? <a href="{{ route('admin.titulars.create') }}" target="_blank">Dodaj novog titulara</a>, pa osvježi ovu stranicu.</small>
                    @error('titular_id') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label for="category_id">Kategorija</label>
                    <select id="category_id" name="category_id">
                        <option value="">— bez kategorije —</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $coupon->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <x-field name="title" label="Naslov kupona" :value="$coupon->title" required placeholder="npr. Popust na sve pizze" />
            <x-field name="discount" label="Popust (veliki tekst na kuponu)" :value="$coupon->discount" maxlength="50"
                     placeholder="npr. -20%"
                     help="Što kraće, najbolje do 6 znakova: -20%, do 30%, 2 za 1, 5 €... Detalje napiši u Opis. Ostavi prazno za oglas partnera (tada nema gumba Iskoristi kupon)." />
            <x-field name="description" label="Opis" type="textarea" :value="$coupon->description" rows="4"
                     help="Na što se popust odnosi. Ako ima više popusta, nabroji ih ovdje." />
            <x-field name="conditions" label="Uvjeti (sitna slova)" type="textarea" :value="$coupon->conditions" rows="3"
                     placeholder="npr. Uz predočenje x-ice. Ne vrijedi za artikle na sniženju." />
        </div>

        <div class="card">
            <h2 style="margin-top:0">Dodatno (nije obavezno)</h2>
            <div class="grid-2">
                <x-field name="code" label="Promo kod" :value="$coupon->code" maxlength="50" placeholder="npr. 30HNK23ST"
                         help="Za online kupnju. Prikazuje se velikim slovima kad student iskoristi kupon." />
                <x-field name="valid_until" label="Vrijedi do" type="date" :value="$coupon->valid_until?->toDateString()"
                         help="Ostavi prazno = vrijedi dok je izdanje aktivno. Nakon datuma se sam sakrije." />
            </div>
            <x-field name="link" label="Link" type="url" :value="$coupon->link" placeholder="https://..."
                     help="Rezervacija, aplikacija, webshop... (umjesto QR koda iz tiskanog oglasa)." />
        </div>

        <div class="card">
            <h2 style="margin-top:0">Vizual (nije obavezno)</h2>
            <p class="muted">Isti format kao u tiskanoj brošuri: uspravni A6 (npr. 1050×1500). Ako ga staviš, prikazuje se umjesto logotipa i teksta.</p>
            @if ($coupon->visual_path)
                <img class="preview" src="{{ Storage::disk('public')->url($coupon->visual_path) }}" alt="">
                <x-checkbox name="remove_visual" label="Ukloni ovu sliku" />
            @endif
            <x-field name="visual" label="Slika oglasa" type="file" accept="image/*" help="Najviše 4 MB." />
        </div>

        <div class="card">
            <x-checkbox name="is_published" label="Objavljen" :checked="$coupon->is_published"
                        help="Makni kvačicu da sakriješ kupon bez brisanja." />
            <x-checkbox name="requires_login" label="Samo za prijavljene studente" :checked="$coupon->requires_login"
                        help="Neprijavljeni posjetitelji ovaj kupon uopće ne vide." />
            <x-field name="sort_order" label="Redoslijed" type="number" :value="$coupon->sort_order" style="max-width:160px"
                     help="Manji broj = prikazuje se ranije." />
        </div>

        <button class="btn primary">Spremi kupon</button>
    </form>

    @if ($coupon->exists)
        <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" style="margin-top:40px"
              onsubmit="return confirm('Sigurno obrisati ovaj kupon?')">
            @csrf @method('DELETE')
            <button class="link danger">Obriši kupon</button>
        </form>
    @endif
</x-admin-layout>
