<x-admin-layout :title="$titular->exists ? $titular->name : 'Novi titular'">
    <div class="head">
        <h1>{{ $titular->exists ? $titular->name : 'Novi titular' }}</h1>
        <a class="btn" href="{{ route('admin.titulars.index') }}">Natrag</a>
    </div>

    <form method="POST" enctype="multipart/form-data"
          action="{{ $titular->exists ? route('admin.titulars.update', $titular) : route('admin.titulars.store') }}">
        @csrf
        @if ($titular->exists) @method('PUT') @endif

        <div class="card">
            <x-field name="name" label="Naziv" :value="$titular->name" required placeholder="npr. Pizzeria Napoli" />

            @if ($titular->logo_path)
                <img class="preview logo" src="{{ Storage::disk('public')->url($titular->logo_path) }}" alt="">
            @endif
            <x-field name="logo" label="Logotip" type="file" accept="image/*" help="Najbolje kvadratna slika. Najviše 4 MB." />

            <div class="grid-2">
                <x-field name="website" label="Web stranica" type="url" :value="$titular->website" placeholder="https://..." />
                <x-field name="instagram" label="Instagram" :value="$titular->instagram" placeholder="@ime" />
            </div>
            <x-field name="notes" label="Bilješke (vidiš samo ti)" type="textarea" :value="$titular->notes" rows="3"
                     help="Kontakt osoba, broj telefona, dogovor..." />
        </div>

        <button class="btn primary">Spremi titulara</button>
    </form>

    @if ($titular->exists)
        <h2>Lokacije</h2>

        @if ($titular->locations->isNotEmpty())
            <div class="table-wrap" style="margin-bottom:16px">
                <table>
                    <tr><th>Adresa</th><th>Napomena</th><th>Na karti</th><th></th></tr>
                    @foreach ($titular->locations as $location)
                        <tr>
                            <td>{{ $location->address }}</td>
                            <td>{{ $location->note }}</td>
                            <td>
                                @if ($location->lat)
                                    <a target="_blank" href="https://www.openstreetmap.org/?mlat={{ $location->lat }}&mlon={{ $location->lng }}#map=18/{{ $location->lat }}/{{ $location->lng }}">Pogledaj</a>
                                @else
                                    <span class="muted">nije označeno</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.locations.destroy', $location) }}"
                                      onsubmit="return confirm('Obrisati ovu lokaciju?')">
                                    @csrf @method('DELETE')
                                    <button class="link danger">Obriši</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif

        @push('head')
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
            <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
        @endpush

        <form class="card" method="POST" action="{{ route('admin.locations.store', $titular) }}" x-data="locationPicker()">
            @csrf
            <h3 style="margin-top:0">Dodaj lokaciju</h3>
            <div class="field">
                <label for="address">Adresa</label>
                <div class="row">
                    <input id="address" type="text" name="address" x-model="address" required placeholder="npr. Ilica 10, Zagreb"
                           @keydown.enter.prevent="find()">
                    <button type="button" class="btn" @click="find()">Pronađi na karti</button>
                </div>
                <small x-text="message || 'Upiši adresu i klikni Pronađi. Ili samo klikni na kartu.'"></small>
                @error('address') <div class="error">{{ $message }}</div> @enderror
            </div>
            <x-field name="note" label="Napomena (nije obavezno)" placeholder="npr. pon–sub 10–21 h, tel. 01/482 5071" />
            <label style="font-weight:600; display:block; margin-bottom:6px">Mjesto na karti</label>
            <div x-ref="map" class="map"></div>
            <small class="muted">Možeš povući pribadaču da točno označiš mjesto.</small>
            <input type="hidden" name="lat" :value="lat">
            <input type="hidden" name="lng" :value="lng">
            <p><button class="btn primary">Dodaj lokaciju</button></p>
        </form>

        <script>
            // Leaflet objects stay outside Alpine's reactive data on purpose.
            function locationPicker() {
                let map, marker;

                return {
                    address: '',
                    lat: '',
                    lng: '',
                    message: '',

                    init() {
                        map = L.map(this.$refs.map).setView([45.8131, 15.9775], 13);
                        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap',
                        }).addTo(map);
                        map.on('click', (e) => this.place(e.latlng.lat, e.latlng.lng));
                    },

                    place(lat, lng) {
                        this.lat = lat.toFixed(7);
                        this.lng = lng.toFixed(7);
                        if (! marker) {
                            marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                            marker.on('dragend', () => {
                                const p = marker.getLatLng();
                                this.place(p.lat, p.lng);
                            });
                        } else {
                            marker.setLatLng([lat, lng]);
                        }
                    },

                    async find() {
                        if (! this.address) return;
                        this.message = 'Tražim...';
                        try {
                            const res = await fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=hr&q='
                                + encodeURIComponent(this.address));
                            const [hit] = await res.json();
                            if (! hit) {
                                this.message = 'Adresa nije pronađena. Označi mjesto klikom na kartu.';
                                return;
                            }
                            this.place(+hit.lat, +hit.lon);
                            map.setView([+hit.lat, +hit.lon], 17);
                            this.message = 'Pronađeno! Provjeri je li pribadača na pravom mjestu.';
                        } catch {
                            this.message = 'Pretraga trenutno ne radi. Označi mjesto klikom na kartu.';
                        }
                    },
                };
            }
        </script>

        <form method="POST" action="{{ route('admin.titulars.destroy', $titular) }}" style="margin-top:40px"
              onsubmit="return confirm('Sigurno obrisati ovog titulara?')">
            @csrf @method('DELETE')
            <button class="link danger">Obriši titulara</button>
        </form>
    @endif
</x-admin-layout>
