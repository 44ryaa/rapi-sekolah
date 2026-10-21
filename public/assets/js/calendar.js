(function () {
  const BULAN = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
  const HARI = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
  const WARNA = ['#423223','#b8824f','#1f7a4a','#ba2c2c','#6b523e','#9c6a3b','#2c221a','#785536'];
  const pad = n => String(n).padStart(2, '0');
  const iso = (y, m, d) => `${y}-${pad(m + 1)}-${pad(d)}`;
  const el = (t, c, txt) => { const x = document.createElement(t); if (c) x.className = c; if (txt != null) x.textContent = txt; return x; };

  const now = new Date();
  let y = now.getFullYear(), m = now.getMonth(), sel = iso(y, m, now.getDate()), byDate = {};
  const daysEl = document.getElementById('days'), label = document.getElementById('monthLabel');
  const heroDate = document.getElementById('calHeroDate');

  async function load() {
    const textLabel = `${BULAN[m]} ${y}`;
    if (label) label.textContent = textLabel;
    if (heroDate) heroDate.textContent = textLabel;

    try {
      const r = await fetch(`${window.RAPI.api}?month=${y}-${pad(m + 1)}`, { headers: { Accept: 'application/json' } });
      const data = await r.json();
      byDate = {};
      (data.events || []).forEach(e => (byDate[e.date] ||= []).push(e));
    } catch (e) { byDate = {}; }
    draw(); list();
  }

  function draw() {
    if (!daysEl) return;
    daysEl.replaceChildren();
    const first = new Date(y, m, 1), lead = (first.getDay() + 6) % 7, total = new Date(y, m + 1, 0).getDate();
    const todayIso = iso(now.getFullYear(), now.getMonth(), now.getDate());
    for (let i = 0; i < lead; i++) daysEl.append(el('span'));
    for (let d = 1; d <= total; d++) {
      const key = iso(y, m, d), evs = byDate[key] || [];
      const b = el('button', 'day' + (key === todayIso ? ' today' : '') + (key === sel ? ' sel' : ''));
      b.type = 'button'; b.setAttribute('aria-label', `${d} ${BULAN[m]}, ${evs.length} agenda`);
      b.append(el('span', 'n', d));
      evs.slice(0, 3).forEach(e => { const bar = el('i', 'bar' + (e.status === 'Diajukan' ? ' pending' : '')); bar.style.background = WARNA[e.room_id % WARNA.length]; b.append(bar); });
      if (evs.length > 3) b.append(el('span', 'more', `+${evs.length - 3}`));
      b.addEventListener('click', () => { sel = key; draw(); list(); });
      daysEl.append(b);
    }
  }

  function list() {
    const [sy, sm, sd] = sel.split('-').map(Number), dt = new Date(sy, sm - 1, sd);
    const dayTitle = document.getElementById('dayTitle');
    if (dayTitle) dayTitle.textContent = `Agenda ${HARI[dt.getDay()]}, ${sd} ${BULAN[sm - 1]} ${sy}`;
    const ul = document.getElementById('dayList'); 
    if (!ul) return;
    ul.replaceChildren();
    const evs = byDate[sel] || [];
    if (!evs.length) { 
      ul.append(el('li', 'empty', 'Tidak ada agenda pada tanggal ini.')); 
      ul.lastChild.style.cssText = 'background:none;border:0'; 
      return; 
    }
    evs.forEach(e => {
      const li = el('li', e.status === 'Diajukan' ? 'pending' : '');
      li.style.borderLeftColor = WARNA[e.room_id % WARNA.length];
      li.append(el('time', '', `${e.start}–${e.end}`), el('span', '', e.name),
        el('span', 'ev-room', e.room + (e.status === 'Diajukan' ? ' · Menunggu Persetujuan Waka' : ' · Disetujui')));
      ul.append(li);
    });
  }

  const go = d => { m += d; if (m < 0) { m = 11; y--; } if (m > 11) { m = 0; y++; } load(); };
  document.getElementById('prev')?.addEventListener('click', () => go(-1));
  document.getElementById('next')?.addEventListener('click', () => go(1));
  document.getElementById('today')?.addEventListener('click', () => { y = now.getFullYear(); m = now.getMonth(); sel = iso(y, m, now.getDate()); load(); });
  document.getElementById('todayHeroBtn')?.addEventListener('click', () => { y = now.getFullYear(); m = now.getMonth(); sel = iso(y, m, now.getDate()); load(); });
  
  load();
})();
