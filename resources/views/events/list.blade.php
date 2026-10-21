@extends('layouts.app')
@section('content')
@php
  $user       = auth()->user();
  $admin      = $user->isAdmin();
  $waka       = $user->isWaka();
  $canApprove = $admin || $waka;
  $guru       = $user->isGuru();

  $cntTotal   = $events->count();
  $cntPending = $events->where('status', 'Diajukan')->count();
  $cntApproved= $events->where('status', 'Disetujui')->count();
@endphp

{{-- ═══════════════ TOP STATS BAR (Image 2 Replica) ═══════════════ --}}
<div class="dashboard-top-grid">
  {{-- Hero / Portfolio Summary Card --}}
  <div class="hero-stat-card">
    <div class="hero-stat-header">
      <span class="hero-stat-title">Peminjaman Ruang</span>
      <span class="hero-stat-badge">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Jadwal Sarpras
      </span>
    </div>
    <div class="hero-stat-value">{{ $cntTotal }} Agenda</div>
    <div class="hero-stat-sub">{{ $cntPending }} pengajuan menunggu persetujuan Waka/Admin</div>

    <div class="hero-stat-pills">
      <button class="filter-pill active" type="button">Semua</button>
      <button class="filter-pill" type="button" onclick="filterEventStatus('Diajukan')">Menunggu ({{ $cntPending }})</button>
      <button class="filter-pill" type="button" onclick="filterEventStatus('Disetujui')">Disetujui ({{ $cntApproved }})</button>
    </div>
  </div>

  {{-- 3 Asset/Status Cards Stack --}}
  <div class="asset-cards-row">
    {{-- Card 1: Menunggu ACC --}}
    <div class="asset-card purple">
      <div class="asset-card-top">
        <div>
          <div class="asset-card-value">{{ $cntPending }} Pengajuan</div>
          <div class="asset-card-sub">Menunggu ACC Waka</div>
        </div>
      </div>
      <div class="asset-card-bottom">
        <div class="asset-icon-pill">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <span class="asset-delta-tag neutral">Review</span>
      </div>
    </div>

    {{-- Card 2: Disetujui --}}
    <div class="asset-card green">
      <div class="asset-card-top">
        <div>
          <div class="asset-card-value">{{ $cntApproved }} Agenda</div>
          <div class="asset-card-sub">Terjadwal &amp; Siap</div>
        </div>
      </div>
      <div class="asset-card-bottom">
        <div class="asset-icon-pill">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <span class="asset-delta-tag pos">Disetujui</span>
      </div>
    </div>

    {{-- Card 3: Ruang Tersedia --}}
    <div class="asset-card gold">
      <div class="asset-card-top">
        <div>
          <div class="asset-card-value">{{ $rooms->count() }} Ruangan</div>
          <div class="asset-card-sub">Sarpras Aktif</div>
        </div>
      </div>
      <div class="asset-card-bottom">
        <div class="asset-icon-pill">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 4H3v16h18V9z"/><polyline points="13 4 13 9 21 9"/></svg>
        </div>
        <span class="asset-delta-tag pos">Siap Pakai</span>
      </div>
    </div>
  </div>
</div>

{{-- ═══════════════ MAIN CONTENT (Split Grid) ═══════════════ --}}
<div class="dashboard-split-grid">
  {{-- Left Column: List of Events --}}
  <div>
    <div class="page-head">
      <div>
        <h2 style="margin:0;font-size:1.25rem">{{ $canApprove ? 'Semua Agenda Sekolah' : 'Agenda Saya' }}</h2>
        <p class="muted" style="margin:.15rem 0 0">
          @if($canApprove)
            Sebagai <b>{{ $waka ? 'Waka' : 'Admin' }}</b>, Anda dapat menyetujui langsung (ACC instan), menolak, atau memindahkan ruang agenda.
          @else
            Pengajuan peminjaman ruang akan ditinjau dan disetujui oleh Waka Sarpras.
          @endif
        </p>
      </div>
      <div style="display:flex;gap:.5rem;flex-wrap:wrap">
        <a class="btn sm ghost" href="{{ route('complaints.create') }}" title="Laporkan kerusakan sarpras">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
          Aduan Kerusakan
        </a>
        <a class="btn sm" href="{{ route('events.create') }}">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Ajukan Agenda
        </a>
      </div>
    </div>

    @if($events->isEmpty())
      <section class="card center">
        <div class="item-icon-squircle" style="margin:0 auto 1rem;width:48px;height:48px">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/></svg>
        </div>
        <h3>Belum Ada Agenda</h3>
        <p class="muted">Belum ada agenda kegiatan atau peminjaman ruang yang diajukan.</p>
        <a class="btn" href="{{ route('events.create') }}" style="margin-top:.5rem">Ajukan Peminjaman Ruang</a>
      </section>
    @endif

    @foreach($events as $ev)
    @php
      $cls = ['Diajukan'=>'warn','Disetujui'=>'ok','Ditolak'=>'bad'][$ev->status] ?? '';
    @endphp
    <article class="card item" data-status="{{ $ev->status }}">
      {{-- Header with squircle icon --}}
      <div class="item-top">
        <div class="item-title-wrap">
          <div class="item-icon-squircle">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          </div>
          <div>
            <h3>{{ $ev->event_name }}</h3>
            <span class="muted" style="font-size:.78rem">{{ $ev->room->room_name }}</span>
          </div>
        </div>
        <span class="badge {{ $cls }}">{{ $ev->status }}</span>
      </div>

      {{-- Info --}}
      <p class="meta">
        {{ $ev->date->translatedFormat('d M Y') }} ·
        <b>{{ $ev->session_label ?? substr($ev->start_time,0,5).'–'.substr($ev->end_time,0,5) }}</b> ·
        {{ $ev->room->room_name }}
        @if($canApprove) · Diajukan oleh: <b>{{ $ev->requester->fullname ?? '-' }}</b> @endif
      </p>

      {{-- Move reason note --}}
      @if($ev->move_reason)
        <p class="note" style="border-left:3.5px solid var(--accent);background:var(--c-cream-soft)">
          <b>Ruang Dipindahkan:</b> {{ $ev->move_reason }}
        </p>
      @endif

      {{-- Reject reason note --}}
      @if($ev->status === 'Ditolak' && $ev->reject_reason)
        <p class="note" style="border-left:3.5px solid var(--bad);background:var(--bad-bg);color:var(--bad-txt)">
          <b>Alasan Ditolak:</b> {{ $ev->reject_reason }}
        </p>
      @endif

      {{-- ── Actions Bar ─────────────────────────────────────────── --}}
      <div class="actions" style="margin-top:.85rem;display:flex;gap:.5rem;flex-wrap:wrap;align-items:center">
        {{-- Tombol Aduan Kerusakan pada peminjaman ruang --}}
        <a class="btn sm ghost" href="{{ route('complaints.create', ['room_id' => $ev->room_id]) }}" title="Laporkan kerusakan sarpras di {{ $ev->room->room_name }}">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
          Aduan Kerusakan
        </a>

        {{-- ── Admin & Waka: Fast Instant AJAX Approval & Reject with Reason ── --}}
        @if($canApprove && $ev->status === 'Diajukan')
          <form method="post" action="{{ route('events.review') }}" class="ajax-approve-form" style="margin:0">@csrf
            <input type="hidden" name="id" value="{{ $ev->id }}">
            <input type="hidden" name="decision" value="approve">
            <button type="submit" class="btn sm" title="Langsung Setujui Agenda">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
              Setujui (ACC)
            </button>
          </form>

          <button class="btn sm danger" type="button"
            data-reject-event="{{ $ev->id }}"
            data-name="{{ $ev->event_name }}"
            data-room="{{ $ev->room->room_name }}"
            title="Tolak Pengajuan Peminjaman dengan Alasan">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            Tolak
          </button>
        @endif

        {{-- ── Admin & Waka: Pindah ruang ── --}}
        @if($canApprove && $ev->status !== 'Ditolak')
          <button class="btn sm ghost" type="button"
            data-move="{{ $ev->id }}"
            data-name="{{ $ev->event_name }}"
            data-room="{{ $ev->room_id }}"
            title="Pindahkan ke Ruangan Lain">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 5h18"/><polyline points="7 23 3 19 7 15"/><path d="M21 19H3"/></svg>
            Pindahkan Ruang
          </button>
        @endif
      </div>
    </article>
    @endforeach
  </div>

  {{-- Right Column: Dark Action Card --}}
  <div>
    <div class="dark-action-banner">
      <span class="banner-badge">SISTEM RESERVASI</span>
      <h3 class="banner-title">Peminjaman Ruang Terjadwal</h3>
      <p class="banner-desc">
        Pemesanan ruang otomatis memeriksa bentrok jadwal sesi sekolah dan diteruskan ke Waka Sarpras untuk persetujuan cepat.
      </p>
      <a href="{{ route('events.create') }}" class="btn-banner-cta">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Ajukan Peminjaman
      </a>
    </div>

    {{-- Rules Card --}}
    <div class="card" style="margin-top:1.25rem">
      <h3 style="font-size:.95rem;margin-bottom:.5rem">Ketentuan Peminjaman</h3>
      <ul style="padding-left:1.2rem;margin:0;font-size:.84rem;color:var(--ink-secondary);line-height:1.7">
        <li>Pengajuan dilakukan minimal H-1 sebelum kegiatan.</li>
        <li>Sistem otomatis mendeteksi bentrok jam dan sesi.</li>
        <li><b>Waka Sarpras</b> dapat menyetujui atau memindahkan ruang.</li>
      </ul>
    </div>
  </div>
</div>

{{-- ── Modal: Tolak peminjaman dengan alasan (Admin & Waka) ──────────── --}}
@if($canApprove)
<dialog id="rejectEventDialog" class="modal" aria-labelledby="rjeTitle">
  <h2 id="rjeTitle" style="margin:0 0 .4rem">Tolak Peminjaman Ruang</h2>
  <p class="muted" id="rjeName" style="margin-bottom:1rem"></p>
  <form method="post" action="{{ route('events.review') }}" class="form" id="rejectEventForm">@csrf
    <input type="hidden" name="id" id="rjeId">
    <input type="hidden" name="decision" value="reject">
    <label>Alasan Penolakan Peminjaman
      <textarea name="reject_reason" id="rjeReason" rows="3" minlength="5" required
        placeholder="Tulis alasan penolakan... Contoh: Ruang sedang dalam pemeliharaan sarpras / digunakan kegiatan resmi dinas."></textarea>
    </label>
    <p class="muted" style="font-size:.8rem">Peminjam akan menerima pemberitahuan otomatis berisi alasan penolakan ini.</p>
    <div class="actions" style="margin-top:1rem">
      <button class="btn danger" type="submit">Konfirmasi Tolak Peminjaman</button>
      <button class="btn ghost" type="button" data-close>Batal</button>
    </div>
  </form>
</dialog>

{{-- ── Modal: Pindah ruang (Admin & Waka) ──────────────────────────── --}}
<dialog id="moveDialog" class="modal" aria-labelledby="mvTitle">
  <h2 id="mvTitle" style="margin:0 0 .4rem">Pindah Ruang Agenda</h2>
  <p class="muted" id="moveName" style="margin-bottom:1rem"></p>
  <form method="post" action="{{ route('events.move') }}" class="form">@csrf
    <input type="hidden" name="id" id="moveId">
    <label>Ruangan Tujuan
      <select name="new_room_id" id="moveRoom" required>
        @foreach($rooms as $r)
          <option value="{{ $r->id }}">{{ $r->room_name }} ({{ $r->location }})</option>
        @endforeach
      </select>
    </label>
    <label>Alasan Pemindahan
      <textarea name="reason" rows="3" minlength="5" required
        placeholder="Contoh: Ruang aula dipakai dinas pendidikan mendadak."></textarea>
    </label>
    <p class="muted" style="font-size:.8rem">Peminjam akan menerima notifikasi otomatis berisi ruang baru.</p>
    <div class="actions" style="margin-top:1rem">
      <button class="btn" type="submit">Konfirmasi Pindahkan</button>
      <button class="btn ghost" type="button" data-close>Batal</button>
    </div>
  </form>
</dialog>

<script>
document.querySelectorAll('[data-reject-event]').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('rjeId').value = btn.dataset.rejectEvent;
    document.getElementById('rjeName').textContent = 'Kegiatan: ' + btn.dataset.name + ' (' + btn.dataset.room + ')';
    document.getElementById('rjeReason').value = '';
    document.getElementById('rejectEventDialog').showModal();
  });
});

document.querySelectorAll('[data-move]').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('moveId').value = btn.dataset.move;
    document.getElementById('moveName').textContent = 'Pindahkan: ' + btn.dataset.name;
    document.getElementById('moveRoom').value = btn.dataset.room;
    document.getElementById('moveDialog').showModal();
  });
});

function filterEventStatus(status) {
  document.querySelectorAll('article.card.item').forEach(el => {
    if (!status || el.dataset.status === status) {
      el.style.display = '';
    } else {
      el.style.display = 'none';
    }
  });
}
</script>
@endif
@endsection
