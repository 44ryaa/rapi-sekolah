(function () {
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];

  // Pop-up notifikasi otomatis (mis. ruangan dipindah Waka)
  const nd = $('#notifDialog'); if (nd && nd.showModal) nd.showModal();

  // Tutup dialog
  $$('[data-close]').forEach(b => b.addEventListener('click', () => b.closest('dialog').close()));
  // Konfirmasi aksi berbahaya
  $$('form[data-confirm]').forEach(f => f.addEventListener('submit', e => { if (!confirm(f.dataset.confirm)) e.preventDefault(); }));

  // Pindah ruang (Waka)
  $$('[data-move]').forEach(b => b.addEventListener('click', () => {
    $('#moveId').value = b.dataset.move; $('#moveName').textContent = b.dataset.name;
    const sel = $('#moveRoom'); [...sel.options].forEach(o => { o.disabled = o.value === b.dataset.room; }); sel.value = '';
    sel.selectedIndex = [...sel.options].findIndex(o => !o.disabled);
    $('#moveDialog').showModal();
  }));

  // CRUD pengguna
  $$('[data-user]').forEach(b => b.addEventListener('click', () => {
    const d = JSON.parse(b.dataset.user || '{}'), isNew = !d.id;
    $('#userTitle').textContent = isNew ? 'Tambah pengguna' : 'Ubah pengguna';
    $('#uId').value = d.id || 0; $('#uUser').value = d.username || ''; $('#uName').value = d.fullname || '';
    $('#uPhone').value = d.phone_number || ''; $('#uRole').value = d.role_id || 3; $('#uPass').value = '';
    $('#uPass').required = isNew; $('#uPassHint').textContent = isNew ? 'Minimal 8 karakter.' : 'Kosongkan jika tidak ingin mengganti password.';
    $('#userDialog').showModal();
  }));

  // CRUD ruang
  $$('[data-room]').forEach(b => b.addEventListener('click', () => {
    if (!$('#roomDialog')) return;
    const d = JSON.parse(b.dataset.room || '{}');
    $('#roomTitle').textContent = d.id ? 'Ubah ruang' : 'Tambah ruang';
    $('#rId').value = d.id || 0; $('#rName').value = d.room_name || ''; $('#rCap').value = d.capacity || 0;
    $('#rLoc').value = d.location || ''; $('#rPic').value = d.pic_id || 0; $('#rActive').checked = d.id ? d.is_active == 1 : true;
    $('#roomDialog').showModal();
  }));

  // Pratinjau foto bukti
  const photo = $('#photo'), prev = $('#preview');
  if (photo) photo.addEventListener('change', () => {
    const f = photo.files[0]; if (!f) { prev.hidden = true; return; }
    prev.src = URL.createObjectURL(f); prev.hidden = false;
  });

  // Form peminjaman: tampilkan slot terisi & kunci tombol bila bentrok (server tetap memvalidasi ulang)
  const form = $('#eventForm');
  if (form) {
    const room = $('#room'), date = $('#date'), start = $('#start'), end = $('#end');
    const box = $('#slotBox'), err = $('#slotError'), btn = $('#submitBtn');
    let slots = [];
    const check = () => {
      let msg = '';
      if (start.value && end.value && end.value <= start.value) msg = 'Jam selesai harus setelah jam mulai.';
      else if (start.value && end.value) {
        const c = slots.find(s => start.value < s.end && end.value > s.start);
        if (c) msg = `Bentrok dengan "${c.name}" (${c.start}–${c.end}). Pilih jam lain.`;
      }
      err.textContent = msg; err.hidden = !msg; btn.disabled = !!msg;
      [start, end].forEach(i => i.classList.toggle('bad', !!msg));
    };
    const load = async () => {
      slots = []; box.hidden = true;
      if (!room.value || !date.value) return check();
      try {
        const r = await fetch(`${RAPI.slots}&room_id=${encodeURIComponent(room.value)}&date=${encodeURIComponent(date.value)}`, { headers: { Accept: 'application/json' } });
        slots = (await r.json()).slots || [];
      } catch (e) { slots = []; }
      box.replaceChildren();
      if (slots.length) {
        const t = document.createElement('b'); t.textContent = 'Jam yang sudah terisi di ruang ini:'; box.append(t);
        slots.forEach(s => { const p = document.createElement('div'); p.textContent = `${s.start}–${s.end} · ${s.name}`; box.append(p); });
        box.hidden = false;
      } else { box.hidden = false; box.textContent = 'Belum ada agenda di ruang dan tanggal ini.'; }
      check();
    };
    [room, date].forEach(i => i.addEventListener('change', load));
    [start, end].forEach(i => i.addEventListener('input', check));
  }
})();
