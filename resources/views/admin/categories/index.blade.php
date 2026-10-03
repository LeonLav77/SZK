<x-admin-layout title="Kategorije">
    <div class="head">
        <div>
            <h1>Kategorije</h1>
            <p class="muted">Studenti po njima filtriraju kupone.</p>
        </div>
    </div>

    <form class="card row" method="POST" action="{{ route('admin.categories.store') }}" style="align-items:flex-start">
        @csrf
        <div style="flex:1">
            <input type="text" name="name" value="{{ old('name') }}" required placeholder="Nova kategorija, npr. Hrana i piće">
            @error('name') <div class="error" style="color:var(--red)">{{ $message }}</div> @enderror
        </div>
        <button class="btn primary">Dodaj</button>
    </form>

    <div class="table-wrap">
        <table>
            <tr><th>Naziv</th><th>Redoslijed</th><th>Kuponi</th><th></th><th></th></tr>
            @forelse ($categories as $category)
                <tr>
                    <td><strong>{{ $category->name }}</strong></td>
                    <td>{{ $category->sort_order }}</td>
                    <td>{{ $category->coupons_count }}</td>
                    <td><a href="{{ route('admin.categories.edit', $category) }}">Uredi</a></td>
                    <td>
                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                              onsubmit="return confirm('Obrisati kategoriju? Kuponi ostaju, samo bez kategorije.')">
                            @csrf @method('DELETE')
                            <button class="link danger">Obriši</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">Nema kategorija.</td></tr>
            @endforelse
        </table>
    </div>
</x-admin-layout>
