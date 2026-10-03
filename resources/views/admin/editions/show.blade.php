<x-admin-layout :title="$edition->name">
    <div class="head">
        <div>
            <h1>{{ $edition->name }} <span class="badge {{ $edition->status }}">{{ $edition->statusLabel() }}</span></h1>
            @if ($edition->isActive())
                <p class="muted">Ovo izdanje studenti trenutno vide.</p>
            @else
                <p class="muted">Studenti ovo izdanje ne vide dok ga ne aktiviraš.</p>
            @endif
        </div>
        <div class="actions">
            @unless ($edition->isActive())
                <form method="POST" action="{{ route('admin.editions.activate', $edition) }}"
                      onsubmit="return confirm(@js("Aktivirati \"{$edition->name}\"?\n\nStudenti će odmah vidjeti ovo izdanje, a trenutno aktivno će se arhivirati."))">
                    @csrf
                    <button class="btn yellow">Aktiviraj izdanje</button>
                </form>
            @endunless
            <a class="btn" href="{{ route('admin.editions.edit', $edition) }}">Uredi tekstove i PDF</a>
            <a class="btn" href="{{ route('admin.editions.export', $edition) }}">Preuzmi statistiku (Excel)</a>
        </div>
    </div>

    <div class="head">
        <h2 style="margin:0">Kuponi ({{ $edition->coupons->count() }})</h2>
        <a class="btn primary" href="{{ route('admin.editions.coupons.create', $edition) }}">+ Dodaj kupon</a>
    </div>

    <div class="table-wrap">
        <table>
            <tr><th></th><th>Titular</th><th>Kupon</th><th>Popust</th><th>Kategorija</th><th>Vidljivost</th><th>Korištenja</th><th></th></tr>
            @forelse ($edition->coupons as $coupon)
                @php($image = $coupon->visual_path ?? $coupon->titular->logo_path)
                <tr>
                    <td>
                        @if ($image)
                            <img class="thumb" src="{{ Storage::disk('public')->url($image) }}" alt="">
                        @else
                            <span class="thumb"></span>
                        @endif
                    </td>
                    <td>{{ $coupon->titular->name }}</td>
                    <td><a href="{{ route('admin.coupons.edit', $coupon) }}"><strong>{{ $coupon->title }}</strong></a></td>
                    <td>
                        @if ($coupon->isRedeemable()) {{ $coupon->discount }} @else <span class="badge">Oglas partnera</span> @endif
                    </td>
                    <td>{{ $coupon->category?->name ?? '—' }}</td>
                    <td>
                        @unless ($coupon->is_published) <span class="badge">Skriven</span> @endunless
                        @if ($coupon->requires_login) <span class="badge">Samo prijavljeni</span> @endif
                        @if ($coupon->isExpired())
                            <span class="badge draft">Isteklo {{ $coupon->valid_until->format('d.m.') }}</span>
                        @elseif ($coupon->valid_until)
                            <small class="muted">do {{ $coupon->valid_until->format('d.m.Y.') }}</small>
                        @endif
                        @if ($coupon->is_published && ! $coupon->requires_login && ! $coupon->isExpired()) Svi @endif
                    </td>
                    <td>{{ $coupon->redemptions_count }}</td>
                    <td><a href="{{ route('admin.coupons.edit', $coupon) }}">Uredi</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="muted">Još nema kupona u ovom izdanju.</td></tr>
            @endforelse
        </table>
    </div>

    @unless ($edition->isActive())
        <form method="POST" action="{{ route('admin.editions.destroy', $edition) }}" style="margin-top:40px"
              onsubmit="return confirm('Sigurno obrisati cijelo izdanje i sve njegove kupone?')">
            @csrf @method('DELETE')
            <button class="link danger">Obriši ovo izdanje</button>
        </form>
    @endunless
</x-admin-layout>
