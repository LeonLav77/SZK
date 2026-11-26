/* Public site: hash router + views from the design reference, data from /api/v1. */
const API = '/api/v1';
const SITE = 'Studentska zlatna karta';
const app = document.getElementById('app');
const nav = document.getElementById('nav');
const chev = '<svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';

let timer = null;
let routeId = 0;
let coupons = [];
let activeCategory = null;

/* ====== HELPERS ====== */
const pad = n => String(n).padStart(2, '0');
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
const host = url => { try { return new URL(url).hostname.replace(/^www\./, ''); } catch { return url; } };
/** A coupon can be in several categories. */
const inCategory = (c, id) => (c.categories || []).some(cat => cat.id === id);
const extLink = (url, text) => `<a href="${esc(url)}" target="_blank" rel="noopener">${esc(text)}</a>`;

/** GET from the API, cached for the page's lifetime. Resolves to `data` or null. */
const cache = new Map();
function get(path) {
  if (!cache.has(path)) {
    const req = fetch(API + path, { headers: { Accept: 'application/json' } })
      .then(r => (r.ok ? r.json().then(j => j.data) : null))
      .catch(() => null);
    req.then(data => { if (data === null) cache.delete(path); });
    cache.set(path, req);
  }
  return cache.get(path);
}

const icon = d => `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${d}</svg>`;
const NAV = [
  // [section, href, desktop label, phone label, icon]
  ['home', '#/', 'Početna', 'Početna',
    icon('<path d="M4 10.5 12 4l8 6.5V19a1 1 0 0 1-1 1h-4.5v-5.5h-5V20H5a1 1 0 0 1-1-1z"/>')],
  ['kuponi', '#/kuponi', 'Kuponi', 'Kuponi',
    icon('<path d="M4 6h16a1 1 0 0 1 1 1v3a2 2 0 0 0 0 4v3a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-3a2 2 0 0 0 0-4V7a1 1 0 0 1 1-1z"/><path d="M15 6v2M15 11v2M15 16v2"/>')],
  ['karta', '#/karta', 'Karta', 'Karta',
    icon('<path d="M9 4 3 6v14l6-2 6 2 6-2V4l-6 2-6-2z"/><path d="M9 4v14M15 6v14"/>')],
  ['o-projektu', '#/o-projektu', 'O projektu', 'O projektu',
    icon('<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 7.5h.01"/>')],
];

function renderNav(section) {
  const cur = k => (k === section ? ' aria-current="page"' : '');
  nav.innerHTML = NAV.map(([k, h, label]) => `<a href="${h}"${cur(k)}>${label}</a>`).join('');
  document.getElementById('tabbar').innerHTML = NAV
    .map(([k, h, , short, ic]) => `<a href="${h}"${cur(k)}><span class="tab-ic">${ic}</span><span class="tab-l">${short}</span></a>`)
    .join('');
}

/* ====== VIEWS (each returns { html, title?, after? }) ====== */
const FEATURED = 6;

/** Landing page: the hook. Short pitch, real numbers, ways in, then a pointer to O projektu. */
async function viewHome() {
  const [list, issues, settings] = await Promise.all([get('/coupons'), get('/print-issues'), get('/settings')]);
  const all = list || [];
  const issue = (issues || [])[0];
  const mins = Math.round((settings?.countdown_seconds || 0) / 60);

  return { html: `
  <section class="landing-hero">
    <div class="landing-title">
      <p class="eyebrow">Studentska</p>
      <h1 tabindex="-1">Zlatna<br>karta</h1>
      <p class="pitch">Popusti za studente u Zagrebu. Hrana, sport, kultura i usluge na jednom mjestu.</p>
    </div>
    <a class="pill" href="#/kuponi">Pogledaj kupone</a>
  </section>

  <section class="block" aria-labelledby="kako-h">
    <h2 id="kako-h">Kako radi?</h2>
    <ol class="steps">
      <li><b>Odaberi kupon</b><span>Prolistaj popuste ili filtriraj po kategoriji.</span></li>
      <li><b>Klikni „Iskoristi”</b><span>Pokaži ekran na blagajni${mins ? `, kupon traje ${mins} min` : ''}.</span></li>
      <li><b>Ponesi x-icu</b><span>Ili indeks. Vrijedi za sve studente, bez obzira na mjesto stanovanja.</span></li>
    </ol>
  </section>

  ${all.length ? `
  <section class="block featured" aria-labelledby="izdv-h">
    <div class="featured-in">
      <div class="block-head"><h2 id="izdv-h">Izdvojeno</h2><a href="#/kuponi">Svi kuponi →</a></div>
      <div class="rail">
        ${[...all.filter(c => c.discount), ...all.filter(c => !c.discount)].slice(0, FEATURED).map(tile).join('')}
        ${all.length > FEATURED ? `<a class="tile rail-more" href="#/kuponi"><b>+${all.length - FEATURED}</b><span>Svi kuponi →</span></a>` : ''}
      </div>
    </div>
  </section>` : ''}

  ${issue ? `
  <section class="block issue" aria-labelledby="izd-h">
    <a class="issue-cover" href="#/izdanja/${issue.id}" tabindex="-1" aria-hidden="true">
      ${issue.cover_url ? `<img src="${esc(issue.cover_url)}" alt="">` : `<span>${esc(issue.name)}</span>`}
    </a>
    <div>
      <h2 id="izd-h">Tiskano izdanje</h2>
      <p>Sve kupone imaš i na papiru. Najnovije izdanje: <b>${esc(issue.name.replace(/\.$/, ''))}</b>.</p>
      <a class="pill outline" href="#/izdanja/${issue.id}">Listaj izdanje</a>
    </div>
  </section>` : ''}

  <a class="more" href="#/o-projektu"><span>Tko stoji iza ovoga?</span><b>O projektu →</b></a>` };
}

async function viewAbout() {
  const [edition, settings] = await Promise.all([get('/edition'), get('/settings')]);
  const s = settings || {};
  const insta = (s.contact_instagram || '').replace(/^@/, '');

  return { title: 'O projektu', html: `
  <div class="home">
    <section class="hero">
      <h1 tabindex="-1">${esc(edition?.about_title || 'O projektu')}</h1>
      ${edition?.about_html ? `<div class="hero-copy">${edition.about_html}</div>` : ''}
      <a class="pill outline about-issues" href="#/izdanja">Tiskana izdanja →</a>
    </section>
    <section class="contact" aria-labelledby="kontakt-h">
      <h2 id="kontakt-h">kontakt</h2>
      ${s.contact_email ? `<p>mail: <a href="mailto:${esc(s.contact_email)}">${esc(s.contact_email)}</a></p>` : ''}
      ${insta ? `<p>instagram: ${extLink('https://instagram.com/' + encodeURIComponent(insta), insta)}</p>` : ''}
      ${s.contact_link ? `<p>web: ${extLink(s.contact_link, host(s.contact_link))}</p>` : ''}
    </section>
  </div>` };
}

async function viewEditions() {
  const editions = (await get('/print-issues')) || [];

  return { title: 'Tiskana izdanja', html: `
  <a class="back" href="#/o-projektu">${chev}O projektu</a>
  <h1 class="page-h" tabindex="-1">Tiskana izdanja</h1>
  <p class="lead">Odaberi izdanje i listaj ga u PDF-u.</p>
  ${editions.length ? `<div class="covers">
    ${editions.map(e => `
      <a class="cover" href="#/izdanja/${e.id}">
        ${e.cover_url ? `<div class="cover-art has-img"><img src="${esc(e.cover_url)}" alt=""></div>` : `<div class="cover-art">Naslovnica<br>${esc(e.name)}</div>`}
        <span class="cover-cap">${esc(e.name)}</span>
      </a>`).join('')}
  </div>` : '<p class="empty">Još nema tiskanih izdanja.</p>'}` };
}

async function viewEdition(id) {
  const e = ((await get('/print-issues')) || []).find(x => x.id === id);
  if (!e) return viewNotFound();

  return { title: e.name, html: `
  <a class="back" href="#/izdanja">${chev}Sva izdanja</a>
  <h1 class="page-h" tabindex="-1">${esc(e.name)}</h1>
  <div class="pdf">${e.pdf_url ? `<iframe src="${esc(e.pdf_url)}" title="${esc(e.name)} (PDF)"></iframe>` : '<p>prikaz izdanja u PDF-u</p>'}</div>
  ${e.pdf_url ? `<div class="pdf-actions"><a class="pill outline" href="${esc(e.pdf_url)}" target="_blank" rel="noopener">Otvori PDF</a></div>` : ''}` };
}

function tile(c) {
  const logo = c.titular.logo_url ? `<span class="logo"><img src="${esc(c.titular.logo_url)}" alt=""></span>` : '<span class="logo" aria-hidden="true">logo</span>';
  // A bare amount ("120 €") reads as a price tag, so label it; % and 1+1 speak for themselves.
  const isPrice = c.discount && /\d/.test(c.discount) && /€|eur|kn/i.test(c.discount) && !/%/.test(c.discount);
  const off = c.discount
    ? `${isPrice ? '<div class="tile-kicker">Studentska cijena</div>' : ''}<div class="tile-off${c.discount.length > 6 ? ' long' : ''}">${esc(c.discount)}</div>`
    : '';
  // No discount = partner ad, not a coupon: tag it and let the title carry the tile.
  const tag = !c.discount && !c.redeemable ? '<span class="tile-tag">Oglas partnera</span>' : '';

  return `
    <a class="tile" href="#/kuponi/${c.id}">
      <div class="tile-top">${logo}<span class="tile-name">${esc(c.titular.name)}</span></div>
      <div class="tile-body">${tag}${off}<div class="tile-what${c.redeemable ? '' : ' partner'}" title="${esc(c.title)}">${esc(c.title)}</div></div>
    </a>`;
}

function tiles() {
  const list = activeCategory ? coupons.filter(c => inCategory(c, activeCategory)) : coupons;
  return list.length ? list.map(tile).join('') : '';
}

async function viewCoupons() {
  const [list, categories] = await Promise.all([get('/coupons'), get('/categories')]);
  coupons = list || [];
  const used = (categories || []).filter(cat => coupons.some(c => inCategory(c, cat.id)));
  if (!used.some(cat => cat.id === activeCategory)) activeCategory = null;

  const chip = (id, name) => `<button type="button" class="chip" data-cat="${id ?? ''}" aria-pressed="${activeCategory === id}">${esc(name)}</button>`;

  return {
    title: 'Kuponi',
    html: `
  <h1 class="page-h" tabindex="-1">Kuponi</h1>
  <p class="lead">Odaberi popust, pa ga iskoristi na blagajni.</p>
  ${used.length > 1 ? `<div class="chips" role="group" aria-label="Kategorije">${chip(null, 'Sve')}${used.map(c => chip(c.id, c.name)).join('')}</div>` : ''}
  ${coupons.length ? `<div class="coupons" id="grid">${tiles()}</div>` : '<p class="empty">Trenutno nema kupona.</p>'}`,
    after() {
      app.querySelectorAll('.chip').forEach(btn => btn.addEventListener('click', () => {
        activeCategory = btn.dataset.cat ? Number(btn.dataset.cat) : null;
        app.querySelectorAll('.chip').forEach(b => b.setAttribute('aria-pressed', String(b === btn)));
        document.getElementById('grid').innerHTML = tiles();
      }));
    },
  };
}

async function viewCoupon(id) {
  const c = await get(`/coupons/${id}`);
  if (!c) return viewNotFound();

  const name = c.titular.name;
  const locations = c.titular.locations || [];
  const where = locations
    .map(l => `<a class="addr" href="${esc(mapsUrl(l))}" target="_blank" rel="noopener">${pin}<span>${esc(l.address)}</span></a>`
      + (l.note ? `<span class="addr-note">${esc(l.note)}</span>` : ''))
    .join('');
  const mapped = locations.filter(l => l.lat && l.lng);
  const facts = [
    ['Opis', esc(c.description || c.title)],
    ['Koliko', esc(c.discount)],
    ['Uvjeti', esc(c.conditions)],
    // Redeemable coupons reveal the code on the "Iskoristi kupon" screen.
    ['Promo kod', c.code && c.redeemable ? 'Dobivaš ga kad klikneš Iskoristi kupon' : esc(c.code)],
    ['Gdje', where],  ].filter(([, value]) => value);

  return { title: name, html: `
  <div class="detail">
    <a class="back" href="#/kuponi">${chev}Svi kuponi</a>
    ${c.visual_url
      ? `<a class="visual has-img" href="${esc(c.visual_url)}" target="_blank" rel="noopener" title="Otvori vizual u punoj veličini"><img src="${esc(c.visual_url)}" alt="Vizual partnera ${esc(name)}"></a>`
      : `<div class="visual" role="img" aria-label="Vizual partnera ${esc(name)}">vizual</div>`}
    <h1 tabindex="-1">${esc(name)}</h1>
    ${c.discount ? `<span class="detail-off">${esc(c.discount)}</span>` : ''}
    <dl class="facts">
      ${facts.map(([label, value]) => `<div><dt>${label}</dt><dd>${value}</dd></div>`).join('')}
    </dl>
    ${mapped.length ? '<div class="loc-map" id="loc-map" role="region" aria-label="Lokacije na karti"></div>' : ''}
    ${c.redeemable ? `<div class="detail-cta" id="cta"><a class="pill" href="#/kuponi/${c.id}/iskoristi">Iskoristi kupon</a></div>
    <button type="button" class="scroll-hint" id="scroll-hint" hidden>
      Iskoristi kupon
      <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 6l5 5 5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>` : ''}
  </div>`,
    after() {
      bindScrollHint();
      if (mapped.length) showLocationsMap(mapped);
    },
  };
}

/* ====== MAPS ====== */
const pin = '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.5" stroke="currentColor" stroke-width="2"/></svg>';

/** Google Maps link: opens the maps app on phones. Pin if we have one, else search the address. */
const mapsUrl = l => 'https://www.google.com/maps/search/?api=1&query='
  + encodeURIComponent(l.lat && l.lng ? `${l.lat},${l.lng}` : l.address);

/** Leaflet is only needed on coupon pages with mapped locations, so it loads on first use. */
let leafletReady = null;
function loadLeaflet() {
  leafletReady ??= new Promise((resolve, reject) => {
    const css = document.createElement('link');
    css.rel = 'stylesheet';
    css.href = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css';
    document.head.append(css);
    const js = document.createElement('script');
    js.src = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js';
    js.onload = resolve;
    js.onerror = () => { leafletReady = null; reject(); };
    document.head.append(js);
  });
  return leafletReady;
}

const ZAGREB = [45.8131, 15.9775];

/** Loads Leaflet and makes a map in #id. Resolves to null if Leaflet can't load or the page changed. */
async function makeMap(id, options = {}) {
  try { await loadLeaflet(); } catch { document.getElementById(id)?.remove(); return null; }
  const el = document.getElementById(id);
  if (!el) return null; // navigated away while loading
  const map = L.map(el, { scrollWheelZoom: false, ...options });
  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap', maxZoom: 19 }).addTo(map);
  return map;
}

/** Shows all points; `extraZoom` zooms further in from that, centred on the same spot. */
function fitTo(map, points, extraZoom = 0) {
  if (!points.length) map.setView(ZAGREB, 13);
  else if (points.length === 1) map.setView(points[0], 16);
  else {
    map.fitBounds(points, { padding: [40, 40], maxZoom: 16, animate: false });
    if (extraZoom) map.setZoom(Math.min(map.getZoom() + extraZoom, 16), { animate: false });
  }
}

const directions = l => `<a class="pop-dir" href="${esc(mapsUrl(l))}" target="_blank" rel="noopener">Upute do ovdje →</a>`;

/** Small map on the coupon page: one pin per location of this titular. */
async function showLocationsMap(locations) {
  const map = await makeMap('loc-map');
  if (!map) return;
  const icon = L.divIcon({ className: 'map-pin', html: pin, iconSize: [34, 34], iconAnchor: [17, 32], popupAnchor: [0, -28] });

  locations.forEach(l => L.marker([l.lat, l.lng], { icon }).addTo(map).bindPopup(
    `<strong>${esc(l.address)}</strong>${l.note ? `<br>${esc(l.note)}` : ''}<br>${directions(l)}`));
  fitTo(map, locations.map(l => [l.lat, l.lng]));
}

/** Karta: every mapped location of every visible coupon, filterable by category. */
async function viewMap() {
  const [list, categories] = await Promise.all([get('/coupons'), get('/categories')]);
  coupons = list || [];
  const used = (categories || []).filter(cat => coupons.some(c => inCategory(c, cat.id)));
  if (!used.some(cat => cat.id === activeCategory)) activeCategory = null;
  const chip = (id, name) => `<button type="button" class="chip" data-cat="${id ?? ''}" aria-pressed="${activeCategory === id}">${esc(name)}</button>`;

  return {
    title: 'Karta',
    html: `
  <h1 class="page-h" tabindex="-1">Karta</h1>
  <p class="lead">Svi popusti oko tebe. Klikni oznaku za detalje.</p>
  ${used.length > 1 ? `<div class="chips" role="group" aria-label="Kategorije">${chip(null, 'Sve')}${used.map(c => chip(c.id, c.name)).join('')}</div>` : ''}
  <div class="big-map-wrap">
    <div class="big-map" id="big-map" role="region" aria-label="Karta popusta"></div>
    <button type="button" class="locate" id="locate" aria-label="Prikaži moju lokaciju">${icon('<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/><circle cx="12" cy="12" r="7"/>')}</button>
  </div>`,
    async after() {
      const map = await makeMap('big-map');
      if (!map) return;
      const layer = L.layerGroup().addTo(map);

      const draw = fit => {
        layer.clearLayers();
        const visible = activeCategory ? coupons.filter(c => inCategory(c, activeCategory)) : coupons;
        // One marker per location; a titular's coupons share its locations.
        const byLocation = new Map();
        visible.forEach(c => (c.titular.locations || []).filter(l => l.lat && l.lng).forEach(l => {
          if (!byLocation.has(l.id)) byLocation.set(l.id, { l, titular: c.titular, list: [] });
          byLocation.get(l.id).list.push(c);
        }));

        const points = [];
        byLocation.forEach(({ l, titular, list }) => {
          // Marker = the titular's logo (initials if there's none), with a count when it has several coupons.
          const initials = titular.name.split(/\s+/).filter(w => /^\p{L}/u.test(w)).slice(0, 2).map(w => w[0]).join('').toUpperCase();
          const face = titular.logo_url ? `<img src="${esc(titular.logo_url)}" alt="">` : `<b>${esc(initials)}</b>`;
          const count = list.length > 1 ? `<i>${list.length}</i>` : '';
          const marker = L.marker([l.lat, l.lng], {
            icon: L.divIcon({ className: 'map-logo', html: `<span>${face}${count}</span>`, iconSize: [44, 52], iconAnchor: [22, 52], popupAnchor: [0, -50] }),
            title: titular.name,
          });
          marker.bindPopup(`
            <strong class="pop-t">${esc(titular.name)}</strong>
            <span class="pop-a">${esc(l.address)}${l.note ? ` · ${esc(l.note)}` : ''}</span>
            <ul class="pop-list">${list.map(c => `<li><a href="#/kuponi/${c.id}">${c.discount ? `<b>${esc(c.discount)}</b> ` : ''}${esc(c.title)}</a></li>`).join('')}</ul>
            ${directions(l)}`, { minWidth: 220 });
          marker.addTo(layer);
          points.push([l.lat, l.lng]);
        });
        if (fit) fitTo(map, points);
      };
      draw(true);

      app.querySelectorAll('.chip').forEach(btn => btn.addEventListener('click', () => {
        activeCategory = btn.dataset.cat ? Number(btn.dataset.cat) : null;
        app.querySelectorAll('.chip').forEach(b => b.setAttribute('aria-pressed', String(b === btn)));
        draw(true);
      }));

      // "Where am I": a blue dot, and the map centres on it.
      let me = null;
      document.getElementById('locate').addEventListener('click', () => map.locate({ setView: true, maxZoom: 15 }));
      map.on('locationfound', e => {
        me?.remove();
        me = L.circleMarker(e.latlng, { radius: 8, color: '#fff', weight: 3, fillColor: '#2a7fff', fillOpacity: 1 }).addTo(map);
      });
      map.on('locationerror', () => alert('Lokaciju nije moguće dohvatiti. Provjeri dopuštenja u pregledniku.'));
    },
  };
}

/** Small "there's more below" nudge; disappears once the Iskoristi kupon button is on screen. */
let hintObserver = null;
function bindScrollHint() {
  const cta = document.getElementById('cta');
  const hint = document.getElementById('scroll-hint');
  if (!cta || !hint || !('IntersectionObserver' in window)) return;
  hint.addEventListener('click', () => cta.scrollIntoView({ behavior: 'smooth', block: 'center' }));
  hintObserver = new IntersectionObserver(([entry]) => { hint.hidden = entry.isIntersecting; });
  hintObserver.observe(cta);
}

/** Logs the use on the server, then runs the countdown locally. */
async function viewRedeem(id) {
  const res = await fetch(`${API}/coupons/${id}/redeem`, { method: 'POST', headers: { Accept: 'application/json' } }).catch(() => null);
  if (!res?.ok) return viewNotFound();
  const { seconds, coupon: c } = (await res.json()).data;

  return {
    title: 'Iskoristi kupon',
    yellow: true,
    html: `
  <div class="redeem${c.code ? ' has-code' : ''}">
    <div>
      <h1 id="rd-h" tabindex="-1">Kupon je aktiviran</h1>
      <p class="sub" id="rd-sub">Pokaži ovaj ekran na blagajni.</p>
      <p class="who">${esc(c.titular.name)}, ${esc(c.discount)}</p>
    </div>
    <div class="rd-timer">
      <div class="pie" id="pie" role="img" aria-label="Preostalo vrijeme kupona"></div>
      <div>
        <p class="timer-l">Trajanje kupona</p>
        <p class="timer" id="left" aria-live="off">${pad(Math.floor(seconds / 60))}:${pad(seconds % 60)}</p>
        <p class="clock" id="clock"></p>
      </div>
    </div>
    <div>
      ${c.code ? `
      <div class="promo">
        <div>
          <p class="promo-l">Promo kod</p>
          <p class="promo-code">${esc(c.code)}</p>
          <p class="promo-hint">Kupuješ online? Upiši ovaj kod.</p>
        </div>
        <button type="button" class="pill dark promo-copy" id="rd-copy" data-code="${esc(c.code)}">Kopiraj</button>
      </div>` : ''}
      <p class="note" id="rd-note">Kupon je potrebno pokazati na blagajni.</p>
      <p id="rd-back" hidden style="margin-top:16px"><a class="pill dark" href="#/kuponi">Natrag na kupone</a></p>
    </div>
  </div>`,
    after: () => startRedeem(seconds),
  };
}

function viewNotFound() {
  return { title: 'Nije pronađeno', html: '<h1 class="page-h" tabindex="-1">Stranica nije pronađena</h1><p class="lead">Provjeri adresu ili se vrati na početnu stranicu.</p><p style="margin-top:20px"><a class="pill outline" href="#/">Početna</a></p>' };
}

function bindCopy() {
  const btn = document.getElementById('rd-copy');
  if (!btn) return;
  btn.addEventListener('click', async () => {
    try {
      await navigator.clipboard.writeText(btn.dataset.code);
      btn.textContent = 'Kopirano!';
    } catch {
      btn.textContent = 'Ne ide, označi kod';
    }
    setTimeout(() => { btn.textContent = 'Kopiraj'; }, 2000);
  });
}

function startRedeem(seconds) {
  bindCopy();
  const total = seconds * 1000;
  const end = Date.now() + total;
  const pie = document.getElementById('pie');

  function tick() {
    const ms = Math.max(0, end - Date.now());
    const left = Math.ceil(ms / 1000);
    pie.style.setProperty('--e', (360 * (1 - ms / total)).toFixed(2));
    document.getElementById('left').textContent = pad(Math.floor(left / 60)) + ':' + pad(left % 60);
    const d = new Date();
    document.getElementById('clock').textContent = 'Sada je ' + pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
    if (ms === 0) {
      clearInterval(timer); timer = null;
      document.getElementById('rd-h').textContent = 'Kupon je istekao';
      document.getElementById('rd-sub').textContent = 'Vrati se na popis i odaberi novi kupon.';
      document.getElementById('rd-note').hidden = true;
      document.querySelector('.promo')?.remove();
      document.getElementById('rd-back').hidden = false;
    }
  }
  tick();
  timer = setInterval(tick, 250);
}

/* ====== ROUTER ====== */
async function route(first) {
  if (timer) { clearInterval(timer); timer = null; }
  hintObserver?.disconnect(); hintObserver = null;
  const id = ++routeId;
  const [a, b, c] = location.hash.replace(/^#\/?/, '').split('/').filter(Boolean);

  let section = 'home';
  let view;
  if (!a) view = viewHome();
  else if (a === 'o-projektu' && !b) { section = 'o-projektu'; view = viewAbout(); }
  else if (a === 'izdanja') { section = 'o-projektu'; // reached from O projektu, no tab of its own
    view = b ? viewEdition(+b) : viewEditions(); }
  else if (a === 'karta' && !b) { section = 'karta'; view = viewMap(); }
  else if (a === 'kuponi') {
    section = 'kuponi';
    view = b && c === 'iskoristi' ? viewRedeem(+b) : b ? viewCoupon(+b) : viewCoupons();
  } else view = viewNotFound();

  renderNav(section);
  const page = await view;
  if (id !== routeId) return; // a newer navigation started meanwhile

  document.documentElement.classList.toggle('yellow', !!page.yellow);
  document.title = page.title ? `${page.title} | ${SITE}` : SITE;
  app.innerHTML = page.html;
  page.after?.();

  if (!first) {
    window.scrollTo(0, 0);
    app.querySelector('h1')?.focus({ preventScroll: true });
  }
}
window.addEventListener('hashchange', () => route(false));
route(true);
