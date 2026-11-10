@extends('layouts.app')
@section('content')
<div class="page-head">
  <div>
    <h2 style="margin:0;font-size:1.25rem">Fasilitas Ruang &amp; PIC</h2>
    <p class="muted" style="margin:.15rem 0 0">Kelola daftar ruangan, kapasitas, dan penanggung jawab perbaikan.</p>
  </div>
  <button class="btn sm" data-room='{}'>
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Tambah Ruangan
  </button>
</div>

<div class="hint">
  Satu PIC dapat ditugaskan untuk beberapa ruangan sarpras. PIC akan menerima notifikasi pengaduan setelah disetujui (ACC) oleh Admin / Waka.
</div>

<div style="display:grid;gap:.85rem">
@foreach($rooms as $r)
<article class="card item">
  <div class="item-top">
    <div class="item-title-wrap">
      <div class="item-icon-squircle">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 4H3v16h18V9z"/><polyline points="13 4 13 9 21 9"/></svg>
      </div>
      <div>
        <h3>{{ $r->room_name }}</h3>
        <span class="muted" style="font-size:.8rem">{{ $r->location ?: 'Gedung Sekolah' }}</span>
      </div>
    </div>
    <span class="badge {{ $r->is_active ? 'ok' : 'bad' }}">
      {{ $r->is_active ? '● Aktif' : 'Nonaktif' }}
    </span>
  </div>

  <p class="meta">
    Kapasitas: <b>{{ (int)$r->capacity }} orang</b> · Lokasi: {{ $r->location ?: '-' }} · PIC Ruang: <b>{{ $r->pic->fullname ?? 'Belum Ditunjuk' }}</b>
  </p>

  <div class="actions" style="display:flex;gap:.5rem;align-items:center">
    <button class="btn sm ghost" data-room="{{ json_encode(['id'=>$r->id,'room_name'=>$r->room_name,'capacity'=>$r->capacity,'location'=>$r->location,'pic_id'=>$r->pic_id,'is_active'=>$r->is_active]) }}">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Ubah Ruang
    </button>
    <form method="post" action="{{ route('admin.rooms.delete', $r->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ruangan {{ $r->room_name }}?')">
      @csrf
      <button class="btn sm danger" type="submit" style="background:rgba(239,68,68,0.1);color:#ef4444;border:1px solid rgba(239,68,68,0.3)">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
        Hapus
      </button>
    </form>
  </div>
</article>
@endforeach
</div>

{{-- Modal Room Form --}}
<dialog id="roomDialog" class="modal">
  <h2 id="roomTitle" style="margin:0 0 1rem">Ruangan</h2>
  <form method="post" action="{{ route('admin.rooms.save') }}" class="form">@csrf
    <input type="hidden" name="id" id="rId" value="0">
    <label>Nama Ruangan
      <input name="room_name" id="rName" required placeholder="Contoh: Lab Komputer 3, Ruang Rapat Lt. 2">
    </label>
    <div class="row2">
      <label>Kapasitas (Orang)
        <input type="number" name="capacity" id="rCap" min="0" value="0">
      </label>
      <label>Lokasi / Gedung
        <input name="location" id="rLoc" placeholder="Contoh: Gedung B Lt. 1">
      </label>
    </div>
    <label>PIC Penanggung Jawab
      <select name="pic_id" id="rPic">
        <option value="0">-- Belum Ditunjuk --</option>
        @foreach($pics as $p)
          <option value="{{ $p->id }}">{{ $p->fullname }} ({{ $p->username }})</option>
        @endforeach
      </select>
    </label>
    <label class="check">
      <input type="checkbox" name="is_active" id="rActive" checked>
      Ruang Aktif (Dapat Dipinjam dalam Agenda)
    </label>
    <div class="actions" style="margin-top:1.25rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem">
      <div style="display:flex;gap:.5rem;align-items:center">
        <button class="btn" type="submit">Simpan Ruang</button>
        <button class="btn ghost" type="button" data-close>Batal</button>
      </div>
      <button class="btn sm danger" type="button" id="btnDeleteRoomInModal" style="background:rgba(239,68,68,0.12);color:#ef4444;border:1px solid rgba(239,68,68,0.3);display:none;align-items:center;gap:5px;cursor:pointer">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
        Hapus Ruangan
      </button>
    </div>
  </form>
</dialog>

{{-- Form Hapus Ruang Tersembunyi untuk Modal --}}
<form id="deleteRoomFormInModal" method="post" action="" style="display:none">
  @csrf
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const delBtn = document.getElementById('btnDeleteRoomInModal');
  const delForm = document.getElementById('deleteRoomFormInModal');

  document.querySelectorAll('[data-room]').forEach(b => {
    b.addEventListener('click', () => {
      const d = JSON.parse(b.dataset.room || '{}');
      if (delBtn) {
        if (d.id) {
          delBtn.style.display = 'inline-flex';
          delBtn.onclick = function() {
            if (confirm(`Apakah Anda yakin ingin menghapus ruangan "${d.room_name}"?`)) {
              delForm.action = `/admin/rooms/${d.id}/delete`;
              delForm.submit();
            }
          };
        } else {
          delBtn.style.display = 'none';
        }
      }
    });
  });
});
</script>
@endpush
@endsection
