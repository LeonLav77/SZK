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
const fmtDate = iso => { const [y, m, d] = iso.split('-').map(Number); return `${d}. ${m}. ${y}.`; };
const host = url => { try { return new URL(url).hostname.replace(/^www\./, ''); } catch { return url; } };
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

function renderNav(section) {
  const items = [
    ['home', '#/', '<span>Home /</span> <span>o projektu</span>'],
    ['izdanja', '#/izdanja', '<span>Tiskana</span> <span>izdanja</span>'],
    ['kuponi', '#/kuponi', '<span>Kuponi</span>'],
  ];
  nav.innerHTML = items.map(([k, h, l]) => `<a href="${h}"${k === section ? ' aria-current="page"' : ''}>${l}</a>`).join('');
}

/* ====== VIEWS (each returns { html, title?, after? }) ====== */
async function viewHome() {
  const [edition, settings] = await Promise.all([get('/edition'), get('/settings')]);
  const s = settings || {};
  const insta = (s.contact_instagram || '').replace(/^@/, '');

  return { html: `
  <div class="home">
    <section class="hero">
      <h1 tabindex="-1">${esc(edition?.about_title || 'O projektu')}</h1>
      ${edition?.about_html ? `<div class="hero-copy">${edition.about_html}</div>` : ''}
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
  const off = c.discount ? `<div class="tile-off${c.discount.length > 6 ? ' long' : ''}">${esc(c.discount)}</div>` : '';

  return `
    <a class="tile" href="#/kuponi/${c.id}">
      <div class="tile-top">${logo}<span class="tile-name">${esc(c.titular.name)}</span></div>
      <div class="tile-body">${off}<div class="tile-what${c.redeemable ? '' : ' partner'}" title="${esc(c.title)}">${esc(c.title)}</div></div>
    </a>`;
}

function tiles() {
  const list = activeCategory ? coupons.filter(c => c.category?.id === activeCategory) : coupons;
  return list.length ? list.map(tile).join('') : '';
}

async function viewCoupons() {
  const [list, categories] = await Promise.all([get('/coupons'), get('/categories')]);
  coupons = list || [];
  const used = (categories || []).filter(cat => coupons.some(c => c.category?.id === cat.id));
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
  const where = (c.titular.locations || [])
    .map(l => esc(l.address) + (l.note ? ` <span>(${esc(l.note)})</span>` : ''))
    .join('<br>');
  const facts = [
    ['Na što', esc(c.description || c.title)],
    ['Koliko', esc(c.discount)],
    ['Vrijedi do', c.valid_until ? fmtDate(c.valid_until) : ''],
    ['Uvjeti', esc(c.conditions)],
    ['Promo kod', esc(c.code)],
    ['Gdje', where],
    ['Link', c.link ? extLink(c.link, host(c.link)) : ''],
  ].filter(([, value]) => value);

  return { title: name, html: `
  <div class="detail">
    <a class="back" href="#/kuponi">${chev}Svi kuponi</a>
    ${c.visual_url
      ? `<div class="visual has-img"><img src="${esc(c.visual_url)}" alt="Vizual partnera ${esc(name)}"></div>`
      : `<div class="visual" role="img" aria-label="Vizual partnera ${esc(name)}">vizual</div>`}
    <h1 tabindex="-1">${esc(name)}</h1>
    ${c.discount ? `<span class="detail-off">${esc(c.discount)}</span>` : ''}
    <dl class="facts">
      ${facts.map(([label, value]) => `<div><dt>${label}</dt><dd>${value}</dd></div>`).join('')}
    </dl>
    ${c.redeemable ? `<div class="detail-cta"><a class="pill" href="#/kuponi/${c.id}/iskoristi">Iskoristi kupon</a></div>` : ''}
  </div>` };
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
  <div class="redeem">
    <div>
      <h1 id="rd-h" tabindex="-1">Kupon je aktiviran</h1>
      <p class="sub" id="rd-sub">Pokaži ovaj ekran na blagajni.</p>
      <p class="who">${esc(c.titular.name)}, ${esc(c.discount)}</p>
    </div>
    <div>
      <div class="pie" id="pie" role="img" aria-label="Preostalo vrijeme kupona"></div>
      <p class="timer-l">Trajanje kupona</p>
      <p class="timer" id="left" aria-live="off">${pad(Math.floor(seconds / 60))}:${pad(seconds % 60)}</p>
      <p class="clock" id="clock"></p>
    </div>
    <div>
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

function startRedeem(seconds) {
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
      document.getElementById('rd-back').hidden = false;
    }
  }
  tick();
  timer = setInterval(tick, 250);
}

/* ====== ROUTER ====== */
async function route(first) {
  if (timer) { clearInterval(timer); timer = null; }
  const id = ++routeId;
  const [a, b, c] = location.hash.replace(/^#\/?/, '').split('/').filter(Boolean);

  let section = 'home';
  let view;
  if (!a) view = viewHome();
  else if (a === 'izdanja') { section = 'izdanja'; view = b ? viewEdition(+b) : viewEditions(); }
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
