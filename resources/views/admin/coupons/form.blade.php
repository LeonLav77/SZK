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
        <p class="required-note"><b>*</b> obavezno polje</p>

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

                <fieldset class="field cat-picker">
                    <legend>Kategorije</legend>
                    <div class="cat-options">
                        @foreach ($categories as $category)
                            <label class="cat-option">
                                <input type="checkbox" name="categories[]" value="{{ $category->id }}" @checked(in_array($category->id, $checked))>
                                <span>{{ $category->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <small>Možeš odabrati više. Kupon se prikazuje pod svakom odabranom.</small>
                    @error('categories') <div class="error">{{ $message }}</div> @enderror
                </fieldset>
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
            <h2 style="margin-top:0">Online kupnja</h2>
            <p class="muted">Samo ako titular prodaje i online. Inače ostavi prazno.</p>
            <x-field name="code" label="Promo kod" :value="$coupon->code" maxlength="50" placeholder="npr. 30HNK23ST"
                     help="Za online kupnju. Prikazuje se velikim slovima kad student iskoristi kupon." />        </div>

        <div class="card">
            <h2 style="margin-top:0">Vizual</h2>
            <p class="muted">Položeni format 3:2 (npr. 1500×1000). Slika drugog oblika izrezat će se po sredini.</p>
            <x-upload name="visual" label="Slika oglasa" :current="$coupon->visual_path" preview-class="wide" required />
        </div>

        <div class="card">
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
