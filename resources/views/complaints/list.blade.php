@extends('layouts.app')
@section('content')
@php
  $user       = auth()->user();
  $admin      = $user->isAdmin();
  $waka       = $user->isWaka();
  $canApprove = $admin || $waka;
  $pic        = $user->isPic();
  $guru       = $user->isGuru();

  // Counters for the Overview cards (Image 2 style)
  $cntTotal   = $cntTotal ?? $complaints->total();
  $cntPending = $cntPending ?? 0;
  $cntProcess = $cntProcess ?? 0;
  $cntDone    = $cntDone ?? 0;

  // Badge classes
  $repairCls  = ['Belum Diperbaiki'=>'bad', 'Menunggu Spare Part'=>'warn', 'Sudah Diperbaiki'=>'ok'];
@endphp

{{-- ═══════════════ TOP STATS BAR (Image 2 Replica) ═══════════════ --}}
<div class="dashboard-top-grid">
  {{-- Hero / Portfolio Summary Card --}}
  <div class="hero-stat-card">
    <div class="hero-stat-header">
      <span class="hero-stat-title">Monitoring Sarpras</span>
      <span class="hero-stat-badge">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Real-time
      </span>
    </div>
    <div class="hero-stat-value">{{ $cntTotal }} Laporan</div>
    <div class="hero-stat-sub">Status perbaikan ditangani dan diperbarui secara eksklusif oleh PIC ruang</div>

    <div class="hero-stat-pills">
      <button class="filter-pill active" type="button" onclick="filterStatus('')">Semua</button>
      <button class="filter-pill" type="button" onclick="filterStatus('Belum Diperbaiki')">Belum ({{ $cntPending }})</button>
      <button class="filter-pill" type="button" onclick="filterStatus('Menunggu Spare Part')">Spare Part ({{ $cntProcess }})</button>
      <button class="filter-pill" type="button" onclick="filterStatus('Sudah Diperbaiki')">Selesai ({{ $cntDone }})</button>
    </div>
  </div>

  {{-- 3 Asset/Status Cards Stack (Image 2 right cards) --}}
  <div class="asset-cards-row">
    {{-- Card 1: Belum Diperbaiki --}}
    <div class="asset-card purple">
      <div class="asset-card-top">
        <div>
          <div class="asset-card-value">{{ $cntPending }} Lap</div>
          <div class="asset-card-sub">Belum Diperbaiki</div>
        </div>
      </div>
      <div class="asset-card-bottom">
        <div class="asset-icon-pill">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <span class="asset-delta-tag neutral">Antrean PIC</span>
      </div>
    </div>

    {{-- Card 2: Menunggu Spare Part --}}
    <div class="asset-card green">
      <div class="asset-card-top">
        <div>
          <div class="asset-card-value">{{ $cntProcess }} Lap</div>
          <div class="asset-card-sub">Menunggu Part</div>
        </div>
      </div>
      <div class="asset-card-bottom">
        <div class="asset-icon-pill">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
        </div>
        <span class="asset-delta-tag pos">Diproses</span>
      </div>
    </div>

    {{-- Card 3: Selesai Diperbaiki --}}
    <div class="asset-card gold">
      <div class="asset-card-top">
        <div>
          <div class="asset-card-value">{{ $cntDone }} Lap</div>
          <div class="asset-card-sub">Sudah Selesai</div>
        </div>
      </div>
      <div class="asset-card-bottom">
        <div class="asset-icon-pill">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <span class="asset-delta-tag pos">100% OK</span>
      </div>
    </div>
  </div>
</div>

{{-- ═══════════════ MAIN CONTENT (Split Grid) ═══════════════ --}}
<div class="dashboard-split-grid">
  {{-- Left Column: List of Complaints --}}
  <div>
    <div class="page-head">
      <div>
        <h2 style="margin:0;font-size:1.25rem">{{ $pic ? 'Pengaduan Ruangan Saya' : 'Daftar Pengaduan Sarpras' }}</h2>
        <p class="muted" style="margin:.15rem 0 0">
          Status penanganan kerusakan sarpras dikelola dan diperbarui secara eksklusif oleh <b>PIC Ruangan</b>.
        </p>
      </div>

      @if($admin || $guru || $waka)
        <a class="btn sm" href="{{ route('complaints.create') }}">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
          Laporkan Kerusakan
        </a>
      @endif
    </div>

    @if($complaints->isEmpty())
      <section class="card center">
        <div class="item-icon-squircle" style="margin:0 auto 1rem;width:48px;height:48px">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <h3>Semua Fasilitas Terawat</h3>
        <p class="muted">{{ $pic ? 'Belum ada pengaduan di ruangan Anda.' : 'Belum ada pengaduan kerusakan yang diajukan.' }}</p>
        @if($admin || $guru || $waka)
          <a class="btn" href="{{ route('complaints.create') }}" style="margin-top:.5rem">Buat Laporan Baru</a>
        @endif
      </section>
    @endif

    @foreach($complaints as $c)
    <article class="card item" data-repair="{{ $c->status_repair }}">
      {{-- Header with squircle icon --}}
      <div class="item-top">
        <div class="item-title-wrap">
          <div class="item-icon-squircle">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
          </div>
          <div>
            <h3>{{ $c->room->room_name }}</h3>
            <span class="muted" style="font-size:.78rem">{{ $c->room->location ?? 'Gedung Sekolah' }}</span>
          </div>
        </div>

        <div style="display:flex;gap:.45rem;flex-wrap:wrap;align-items:center">
          <span class="badge {{ $repairCls[$c->status_repair] ?? 'bad' }}">{{ $c->status_repair }}</span>
        </div>
      </div>

      {{-- Description --}}
      <p style="margin:.65rem 0;line-height:1.55">{!! nl2br(e($c->description)) !!}</p>

      {{-- Photo Attachments (Sebelum & Sesudah Perbaikan) --}}
      @if($c->photo_path || $c->completion_photo_path)
        <div style="display:flex;gap:.85rem;flex-wrap:wrap;margin:.75rem 0">
          @if($c->photo_path)
            <div style="flex:1;min-width:180px;background:rgba(239,68,68,.05);padding:.55rem;border-radius:10px;border:1px solid rgba(239,68,68,.2)">
              <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.35rem">
                <span class="badge bad" style="font-size:.68rem;padding:2px 7px">Foto Kerusakan (Sebelum)</span>
              </div>
              <a href="{{ asset('storage/'.$c->photo_path) }}" target="_blank" rel="noopener">
                <img class="thumb" src="{{ asset('storage/'.$c->photo_path) }}" alt="Foto kerusakan {{ $c->room->room_name }}" loading="lazy" style="max-height:180px;width:100%;object-fit:cover;border-radius:8px">
              </a>
            </div>
          @endif

          @if($c->completion_photo_path)
            <div style="flex:1;min-width:180px;background:rgba(16,185,129,.06);padding:.55rem;border-radius:10px;border:1px solid rgba(16,185,129,.25)">
              <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.35rem">
                <span class="badge ok" style="font-size:.68rem;padding:2px 7px">Bukti Perbaikan (Sesudah)</span>
                @if($c->completed_at)
                  <span class="muted" style="font-size:.68rem">{{ $c->completed_at->format('d/m/Y H:i') }}</span>
                @endif
              </div>
              <a href="{{ asset('storage/'.$c->completion_photo_path) }}" target="_blank" rel="noopener">
                <img class="thumb" src="{{ asset('storage/'.$c->completion_photo_path) }}" alt="Foto selesai perbaikan {{ $c->room->room_name }}" loading="lazy" style="max-height:180px;width:100%;object-fit:cover;border-radius:8px">
              </a>
            </div>
          @endif
        </div>
      @endif

      {{-- Metadata --}}
      <p class="meta">
        Dilaporkan oleh <b>{{ $c->reporter->fullname ?? '-' }}</b> ·
        {{ $c->reported_at->format('d M Y H:i') }} ({{ $c->age_h }} jam lalu)
        · PIC: <b>{{ $c->room->pic->fullname ?? 'Belum Ditunjuk' }}</b>
      </p>

      @if($c->status_repair === 'Sudah Diperbaiki')
        <div style="margin-top:.6rem;padding:.5rem .75rem;background:rgba(16, 185, 129, 0.08);border-left:3.5px solid #10b981;border-radius:6px;font-size:.82rem">
          <strong style="color:#059669">Keterangan Perbaikan (Selesai):</strong>
          <span style="display:block;margin-top:.2rem;color:var(--ink)">{{ $c->repair_note ?: 'Kerusakan telah selesai diperbaiki dan diuji fungsi oleh PIC.' }}</span>
          @if($c->completion_photo_path)
            <span style="display:inline-block;margin-top:.3rem;color:#059669;font-weight:600;font-size:.75rem">
              &#10003; Bukti fisik foto perbaikan telah diverifikasi &amp; tersimpan.
            </span>
          @endif
        </div>
      @elseif($c->repair_note)
        <p class="note"><b>Catatan Penanganan PIC:</b> {{ $c->repair_note }}</p>
      @endif

      {{-- ── WhatsApp reminder (Admin & Waka) ── --}}
      @if($canApprove && $c->status_repair === 'Belum Diperbaiki')
        <div class="actions" style="margin-top:.6rem">
          @if($c->wa)
            <a class="btn wa" href="{{ $c->wa }}" target="_blank" rel="noopener">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
              Kirim Peringatan WhatsApp ke PIC ({{ $c->room->pic->fullname ?? 'PIC' }})
              @if($c->overdue)
                <span style="background:rgba(0,0,0,0.2);padding:1px 6px;border-radius:4px;font-size:.7rem;margin-left:4px">Lewat Batas</span>
              @endif
            </a>
          @else
            <button class="btn wa" disabled style="opacity:.6" title="Tambahkan nomor HP PIC di menu Manajemen Pengguna">
              PIC Belum Memiliki Nomor HP
            </button>
          @endif
        </div>
      @endif

      {{-- ── PIC: Hanya PIC ruangan ini yang dapat mengubah status ─────── --}}
      @if($pic && (int)($c->room->pic_id ?? 0) === (int)$user->id)
        <form method="post" action="{{ route('complaints.status') }}" enctype="multipart/form-data" class="form" style="margin-top:.85rem;background:rgba(225,220,201,.04);padding:.85rem;border-radius:10px;border:1px solid var(--border)">@csrf
          <input type="hidden" name="id" value="{{ $c->id }}">
          <div style="display:flex;flex-wrap:wrap;gap:.75rem;align-items:flex-end">
            <label style="flex:1;min-width:180px">Status Penanganan (PIC)
              <select name="status_repair" id="statusSelect_{{ $c->id }}" onchange="toggleProofField({{ $c->id }})">
                @foreach($statuses as $s)
                  <option value="{{ $s }}" {{ $s === $c->status_repair ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
              </select>
            </label>
            <label style="flex:2;min-width:220px">Catatan Perkembangan
              <input name="repair_note" value="{{ $c->repair_note }}" placeholder="Contoh: Komponen diganti baru & telah diuji coba.">
            </label>
            <button class="btn sm" type="submit">Perbarui Status</button>
          </div>

          {{-- Container Unggah Bukti Foto Selesai (Wajib jika status "Sudah Diperbaiki") --}}
          <div id="proofBox_{{ $c->id }}" style="margin-top:.75rem;padding:.75rem;background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.3);border-radius:8px;{{ $c->status_repair === 'Sudah Diperbaiki' ? '' : 'display:none;' }}">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.35rem">
              <label for="comp_photo_{{ $c->id }}" style="font-weight:700;font-size:.82rem;color:var(--ink);margin:0">
                Foto Bukti Fisik Selesai Diperbaiki <span style="color:#ef4444">*</span>
              </label>
              @if($c->completion_photo_path)
                <span class="badge ok" style="font-size:.68rem">Foto Tersimpan (Bisa Diperbarui)</span>
              @else
                <span class="badge bad" style="font-size:.68rem">Wajib Diunggah</span>
              @endif
            </div>
            <p class="muted" style="font-size:.75rem;margin:0 0 .5rem">
              Ambil foto langsung dengan kamera HP atau unggah file foto (Maks. 5 MB) sebagai bukti fasilitas telah normal kembali.
            </p>
            <input type="file" id="comp_photo_{{ $c->id }}" name="completion_photo" accept="image/*" capture="environment" style="font-size:.82rem">
          </div>
        </form>
      @endif

      {{-- ── Delete (Admin, Waka, atau Guru pelapor) ───────────────────── --}}
      @if($admin || $waka || ($guru && (int)$c->reporter_id === $user->id))
        <form method="post" action="{{ route('complaints.delete') }}" class="form inline"
          style="margin-top:.5rem"
          onsubmit="return confirm('Hapus rekaman pengaduan di {{ addslashes($c->room->room_name) }}?')">@csrf
          <input type="hidden" name="id" value="{{ $c->id }}">
          <button type="submit" class="btn sm ghost danger" style="border:0;color:var(--bad);padding:0;font-size:.78rem">
            Hapus Pengaduan
          </button>
        </form>
      @endif
    </article>
    @endforeach

    {{-- ── Pagination 5 Laporan Kerusakan (Sesuai Catatan Revisi) ── --}}
    @if($complaints->hasPages())
      <div style="margin-top:1.5rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem">
        <span class="muted" style="font-size:.82rem">
          Halaman <b>{{ $complaints->currentPage() }}</b> dari <b>{{ $complaints->lastPage() }}</b> · Total <b>{{ $complaints->total() }}</b> pengaduan (5/halaman)
        </span>
        <div style="display:flex;gap:.35rem">
          @if($complaints->onFirstPage())
            <span class="btn sm ghost" style="opacity:.4;cursor:not-allowed">« Sebelumnya</span>
          @else
            <a href="{{ $complaints->previousPageUrl() }}" class="btn sm ghost">« Sebelumnya</a>
          @endif

          @if($complaints->hasMorePages())
            <a href="{{ $complaints->nextPageUrl() }}" class="btn sm">Berikutnya »</a>
          @else
            <span class="btn sm ghost" style="opacity:.4;cursor:not-allowed">Berikutnya »</span>
          @endif
        </div>
      </div>
    @endif
  </div>

  {{-- Right Column: Dark Action Card (Image 2 Replica) --}}
  <div>
    <div class="dark-action-banner">
      <span class="banner-badge">FITUR KAMERA REALTIME</span>
      <h3 class="banner-title">Ambil Foto Langsung di Tempat</h3>
      <p class="banner-desc">
        Laporkan sarpras yang rusak langsung melalui browser Anda tanpa perlu upload file manual. Dilengkapi preview realtime dan switch kamera.
      </p>
      @if($admin || $guru || $waka)
        <a href="{{ route('complaints.create') }}" class="btn-banner-cta">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/></svg>
          Buka Kamera &amp; Lapor
        </a>
      @else
        <span class="badge" style="background:rgba(255,255,255,.1);color:#fff">Login sebagai Guru / Admin / Waka untuk melapor</span>
      @endif
    </div>

    {{-- Quick Info Card --}}
    <div class="card" style="margin-top:1.25rem">
      <h3 style="font-size:.95rem;margin-bottom:.5rem">Alur Tindak Lanjut Kerusakan</h3>
      <ul style="padding-left:1.2rem;margin:0;font-size:.84rem;color:var(--ink-secondary);line-height:1.7">
        <li>Guru atau staf melaporkan kerusakan fasilitas ruang.</li>
        <li><b>PIC Ruang</b> bertanggung jawab penuh memperbarui status penanganan &amp; catatan perbaikan.</li>
        <li>Pelapor menerima notifikasi pembaruan status secara otomatis.</li>
        <li>Jika &gt; 48 jam masih berstatus <i>Belum Diperbaiki</i>, Admin / Waka dapat mengirimkan peringatan WhatsApp ke PIC.</li>
      </ul>
    </div>
  </div>
</div>

<script>
// Toggle container bukti foto perbaikan saat status 'Sudah Diperbaiki' dipilih
function toggleProofField(id) {
  const sel = document.getElementById('statusSelect_' + id);
  const box = document.getElementById('proofBox_' + id);
  if (!sel || !box) return;
  if (sel.value === 'Sudah Diperbaiki') {
    box.style.display = 'block';
  } else {
    box.style.display = 'none';
  }
}

// Quick Filter helper
function filterStatus(statusRepair) {
  document.querySelectorAll('article.card.item').forEach(el => {
    if (!statusRepair || el.dataset.repair === statusRepair) {
      el.style.display = '';
    } else {
      el.style.display = 'none';
    }
  });

  document.querySelectorAll('.filter-pill').forEach(btn => {
    btn.classList.toggle('active', btn.getAttribute('onclick')?.includes(statusRepair));
  });
}
</script>
@endsection
