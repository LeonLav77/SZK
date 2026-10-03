<x-admin-layout title="Izdanja">
    <div class="head">
        <div>
            <h1>Izdanja</h1>
            <p class="muted">Studenti uvijek vide samo aktivno izdanje.</p>
        </div>
        <a class="btn primary" href="{{ route('admin.editions.create') }}">+ Novo izdanje</a>
    </div>

    <div class="table-wrap">
        <table>
            <tr><th>Naziv</th><th>Status</th><th>Kuponi</th><th>PDF</th><th></th></tr>
            @forelse ($editions as $edition)
                <tr>
                    <td><a href="{{ route('admin.editions.show', $edition) }}"><strong>{{ $edition->name }}</strong></a></td>
                    <td><span class="badge {{ $edition->status }}">{{ $edition->statusLabel() }}</span></td>
                    <td>{{ $edition->coupons_count }}</td>
                    <td>{{ $edition->pdf_path ? 'Da' : '—' }}</td>
                    <td><a href="{{ route('admin.editions.show', $edition) }}">Otvori</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">Još nema izdanja. Klikni "Novo izdanje".</td></tr>
            @endforelse
        </table>
    </div>
</x-admin-layout>
