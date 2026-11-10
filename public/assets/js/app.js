(function () {
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];

  // Dialog close buttons
  $$('[data-close]').forEach(b => b.addEventListener('click', () => b.closest('dialog')?.close()));
  
  // Confirmation dialogs
  $$('form[data-confirm]').forEach(f => f.addEventListener('submit', e => { 
    if (!confirm(f.dataset.confirm)) e.preventDefault(); 
  }));

  // Fast Instant Approval (Zero Delay ACC)
  document.addEventListener('submit', async function (e) {
    const form = e.target;
    if (!form.classList.contains('ajax-approve-form')) return;
    e.preventDefault();

    const submitBtn = form.querySelector('button[type="submit"]') || form.querySelector('button');
    const originalHtml = submitBtn ? submitBtn.innerHTML : '';
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-sm"></span> Memproses...';
    }

    try {
      const formData = new FormData(form);
      const res = await fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      const data = await res.json();
      if (res.ok && data.success) {
        // Find parent card
        const card = form.closest('.card') || form.closest('article');
        if (card) {
          // Update status badge
          const badge = card.querySelector('.badge.warn') || card.querySelector('.badge');
          if (badge) {
            badge.className = 'badge ok';
            badge.textContent = data.status || 'Disetujui';
          }
          // Replace action buttons with instant approved pill
          const actions = form.closest('.actions');
          if (actions) {
            actions.innerHTML = '<span class="approved-tag"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> ' + (data.status || 'Disetujui') + '</span>';
          }
        }
        if (typeof window.showToast === 'function') {
          window.showToast(data.message || 'Persetujuan berhasil disimpan!', 'ok');
        }
      } else {
        if (typeof window.showToast === 'function') {
          window.showToast(data.error || 'Gagal memproses persetujuan.', 'bad');
        } else {
          alert(data.error || 'Gagal memproses persetujuan.');
        }
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalHtml;
        }
      }
    } catch (err) {
      // Fallback to standard submit if fetch fails
      form.classList.remove('ajax-approve-form');
      form.submit();
    }
  });

  // Pindah ruang (Admin / Waka)
  $$('[data-move]').forEach(b => b.addEventListener('click', () => {
    $('#moveId').value = b.dataset.move; 
    $('#moveName').textContent = b.dataset.name;
    const sel = $('#moveRoom'); 
    [...sel.options].forEach(o => { o.disabled = o.value === b.dataset.room; }); 
    sel.value = '';
    sel.selectedIndex = [...sel.options].findIndex(o => !o.disabled);
    $('#moveDialog')?.showModal();
  }));

  // CRUD pengguna (Admin)
  $$('[data-user]').forEach(b => b.addEventListener('click', () => {
    const d = JSON.parse(b.dataset.user || '{}'), isNew = !d.id;
    $('#userTitle').textContent = isNew ? 'Tambah Pengguna Baru' : 'Ubah Data Pengguna';
    $('#uId').value = d.id || 0; 
    $('#uUser').value = d.username || ''; 
    $('#uName').value = d.fullname || '';
    $('#uPhone').value = d.phone_number || ''; 
    $('#uRole').value = d.role_id || 3; 
    $('#uPass').value = '';
    $('#uPass').required = isNew; 
    $('#uPassHint').textContent = isNew ? 'Minimal 6 karakter.' : 'Kosongkan jika tidak ingin mengganti password.';
    $('#userDialog')?.showModal();
  }));

  // CRUD ruang (Admin / Waka)
  $$('[data-room]').forEach(b => b.addEventListener('click', () => {
    if (!$('#roomDialog')) return;
    const d = JSON.parse(b.dataset.room || '{}');
    $('#roomTitle').textContent = d.id ? 'Ubah Informasi Ruang' : 'Tambah Ruang Baru';
    $('#rId').value = d.id || 0; 
    $('#rName').value = d.room_name || ''; 
    $('#rCap').value = d.capacity || 0;
    $('#rLoc').value = d.location || ''; 
    $('#rPic').value = d.pic_id || 0; 
    $('#rActive').checked = d.id ? d.is_active == 1 : true;

    const delBtn = $('#btnDeleteRoomInModal');
    if (delBtn) {
      if (d.id) {
        delBtn.style.display = 'inline-flex';
        delBtn.onclick = () => {
          if (confirm(`Apakah Anda yakin ingin menghapus ruangan "${d.room_name}"?`)) {
            const delForm = $('#deleteRoomFormInModal');
            if (delForm) {
              delForm.action = `/admin/rooms/${d.id}/delete`;
              delForm.submit();
            }
          }
        };
      } else {
        delBtn.style.display = 'none';
      }
    }

    $('#roomDialog')?.showModal();
  }));

  // Standard photo preview (if file input is used)
  const photo = $('#photo'), prev = $('#preview');
  if (photo) photo.addEventListener('change', () => {
    const f = photo.files[0]; 
    if (!f) { if (prev) prev.hidden = true; return; }
    if (prev) {
      prev.src = URL.createObjectURL(f); 
      prev.hidden = false;
    }
  });

  // Form peminjaman: cek slot bentrok
  const form = $('#eventForm');
  if (form) {
    const room = $('#room'), date = $('#date'), start = $('#start'), end = $('#end');
    const box = $('#slotBox'), err = $('#slotError'), btn = $('#submitBtn');
    let slots = [];
    const check = () => {
      let msg = '';
      if (start && end && start.value && end.value && end.value <= start.value) {
        msg = 'Jam selesai harus setelah jam mulai.';
      } else if (start && end && start.value && end.value) {
        const c = slots.find(s => start.value < s.end && end.value > s.start);
        if (c) msg = `Bentrok dengan "${c.name}" (${c.start}–${c.end}). Pilih jam lain.`;
      }
      if (err) { err.textContent = msg; err.hidden = !msg; }
      if (btn) btn.disabled = !!msg;
      if (start) start.classList.toggle('bad', !!msg);
      if (end) end.classList.toggle('bad', !!msg);
    };
    const load = async () => {
      slots = []; 
      if (box) box.hidden = true;
      if (!room || !date || !room.value || !date.value) return check();
      try {
        const r = await fetch(`${window.RAPI.slots}&room_id=${encodeURIComponent(room.value)}&date=${encodeURIComponent(date.value)}`, { 
          headers: { Accept: 'application/json' } 
        });
        slots = (await r.json()).slots || [];
      } catch (e) { slots = []; }
      if (box) {
        box.replaceChildren();
        if (slots.length) {
          const t = document.createElement('b'); 
          t.textContent = 'Jam yang sudah terisi di ruang ini:'; 
          box.append(t);
          slots.forEach(s => { 
            const p = document.createElement('div'); 
            p.textContent = `${s.start}–${s.end} · ${s.name}`; 
            box.append(p); 
          });
          box.hidden = false;
        } else { 
          box.hidden = false; 
          box.textContent = 'Belum ada agenda di ruang dan tanggal ini.'; 
        }
      }
      check();
    };
    if (room && date) [room, date].forEach(i => i.addEventListener('change', load));
    if (start && end) [start, end].forEach(i => i.addEventListener('input', check));
  }
})();
