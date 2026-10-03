# Studentska Zlatna Karta

Laravel API + simple Blade admin. SQLite. The public frontend (plain HTML/CSS + Alpine) comes later and only talks to `/api/v1`.

## Local

```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed   # demo data, admin login: admin@szk.test / password
php artisan storage:link
php artisan serve
```

Admin: http://localhost:8000/admin · Tests: `php artisan test`

## Production (home-lab server)

On your PC (VPN on, `docker login registry.leon-home-lab.ddns.net` once):

```bash
./push.sh
```

On the server, in `~/docker/szk` with only `compose.yml`:

```bash
docker compose pull && docker compose up -d
docker compose exec app php artisan szk:admin damir@example.com --name=Damir
```

Served at https://szk.leon-home-lab.ddns.net through Traefik (`traefik_proxy` network, no ports exposed).
The database and uploads live on the `szk_data` and `szk_uploads` volumes. Production starts empty;
demo data is local only.

## How it works

- **Editions:** one is active at a time and students only ever see that one. A new edition can copy all coupons from an older one. Activating it archives the previous one.
- **Titulari:** companies, entered once and reused across editions. Each can have many locations (address + map pin) for a future map.
- **Coupons:** belong to one edition and one titular. `discount` is free text (20%, 2 za 1, −5 €...). The 1:1 visual is optional. "Samo za prijavljene" coupons are completely hidden from guests (the API returns 404).
- **Uses:** every "Iskoristi kupon" is logged in `redemptions`. Stats show on the edition page, with a CSV export.
- **No email:** if a student forgets their password, the admin sets a new one under Korisnici.

## API v1

| | |
|---|---|
| `GET /api/v1/edition` | active edition + "O projektu" (`about_html`) |
| `GET /api/v1/settings` | contact info + countdown seconds |
| `GET /api/v1/categories` | |
| `GET /api/v1/print-issues` | PDFs + covers of non-draft editions |
| `GET /api/v1/coupons?category=ID` | coupons of the active edition |
| `GET /api/v1/coupons/{id}` | |
| `POST /api/v1/coupons/{id}/redeem` | logs a use, returns `expires_at`, `seconds`, `code` |
| `POST /api/v1/register`, `/login` | returns `{token, user}` |
| `POST /api/v1/logout`, `GET/DELETE /api/v1/me` | `Authorization: Bearer <token>` |
