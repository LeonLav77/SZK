<x-admin-layout title="Početna">
    <div class="head">
        <div>
            <h1>Dobrodošli</h1>
            @if ($edition)
                <p class="muted">Aktivno izdanje: <strong>{{ $edition->name }}</strong></p>
            @else
                <p class="muted">Trenutno nema aktivnog izdanja, pa studenti ne vide ništa.</p>
            @endif
        </div>
        <div class="actions">
            @if ($edition)
                <a class="btn primary" href="{{ route('admin.editions.coupons.create', $edition) }}">+ Dodaj kupon</a>
                <a class="btn" href="{{ route('admin.editions.show', $edition) }}">Otvori aktivno izdanje</a>
            @endif
            <a class="btn" href="{{ route('admin.editions.create') }}">Pripremi novo izdanje</a>
        </div>
    </div>

    <div class="stats">
        <div class="stat"><b>{{ $couponCount }}</b> kupona u izdanju</div>
        <div class="stat"><b>{{ $redemptionCount }}</b> korištenja ukupno</div>
        <div class="stat"><b>{{ $weekCount }}</b> korištenja zadnjih 7 dana</div>
        <div class="stat"><b>{{ $userCount }}</b> registriranih studenata</div>
    </div>

    @if ($topCoupons->isNotEmpty())
        <h2>Najkorišteniji kuponi</h2>
        <table>
            <tr><th>Titular</th><th>Kupon</th><th>Popust</th><th>Korištenja</th></tr>
            @foreach ($topCoupons as $coupon)
                <tr>
                    <td>{{ $coupon->titular->name }}</td>
                    <td><a href="{{ route('admin.coupons.edit', $coupon) }}">{{ $coupon->title }}</a></td>
                    <td>{{ $coupon->discount }}</td>
                    <td>{{ $coupon->redemptions_count }}</td>
                </tr>
            @endforeach
        </table>
    @endif
</x-admin-layout>
