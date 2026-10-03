<x-admin-layout title="Uredi kategoriju">
    <div class="head">
        <h1>Uredi kategoriju</h1>
        <a class="btn" href="{{ route('admin.categories.index') }}">Natrag</a>
    </div>

    <form class="card" method="POST" action="{{ route('admin.categories.update', $category) }}">
        @csrf @method('PUT')
        <x-field name="name" label="Naziv" :value="$category->name" required />
        <x-field name="sort_order" label="Redoslijed" type="number" :value="$category->sort_order" style="max-width:160px"
                 help="Manji broj = prikazuje se ranije." />
        <button class="btn primary">Spremi</button>
    </form>
</x-admin-layout>
