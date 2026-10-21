@extends('layouts.app')
@push('scripts')
<script src="{{ asset('assets/js/calendar.js') }}"></script>
@endpush

@section('content')
@php
  $user = auth()->user();
@endphp

{{-- ═══════════════ TOP STATS BAR (Image 2 Replica) ═══════════════ --}}
<div class="dashboard-top-grid">
  <div class="hero-stat-card">
    <div class="hero-stat-header">
      <span class="hero-stat-title">Jadwal Penggunaan Ruang</span>
      <span class="hero-stat-badge">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Kalender Aktif
      </span>
    </div>
    <div class="hero-stat-value" id="calHeroDate">{{ date('F Y') }}</div>
    <div class="hero-stat-sub">Pantau ketersediaan ruang kelas, lab, aula, dan fasilitas sekolah</div>

    <div class="hero-stat-pills">
      <button class="filter-pill active" type="button" id="todayHeroBtn">Hari Ini</button>
      @auth
        @if($user->isAdmin() || $user->isGuru() || $user->isWaka())
          <a href="{{ route('events.create') }}" class="filter-pill" style="text-decoration:none">+ Ajukan Agenda</a>
        @endif
      @endauth
    </div>
  </div>

  <div class="asset-cards-row">
    <div class="asset-card purple">
      <div class="asset-card-top">
        <div>
          <div class="asset-card-value">Aula &amp; Lab</div>
          <div class="asset-card-sub">Fasilitas Utama</div>
        </div>
      </div>
      <div class="asset-card-bottom">
        <div class="asset-icon-pill">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
        </div>
        <span class="asset-delta-tag pos">Tersedia</span>
      </div>
    </div>

    <div class="asset-card green">
      <div class="asset-card-top">
        <div>
          <div class="asset-card-value">6 Sesi</div>
          <div class="asset-card-sub">Jadwal Tetap / Hari</div>
        </div>
      </div>
      <div class="asset-card-bottom">
        <div class="asset-icon-pill">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <span class="asset-delta-tag pos">07:00–16:00</span>
      </div>
    </div>

    <div class="asset-card gold">
      <div class="asset-card-top">
        <div>
          <div class="asset-card-value">Waka Sarpras</div>
          <div class="asset-card-sub">Otorisasi &amp; ACC</div>
        </div>
      </div>
      <div class="asset-card-bottom">
        <div class="asset-icon-pill">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <span class="asset-delta-tag neutral">Verifikasi</span>
      </div>
    </div>
  </div>
</div>

{{-- ═══════════════ MAIN CALENDAR & DAY AGENDA (Split Layout) ═══════════════ --}}
<div class="dashboard-split-grid">
  {{-- Left Column: Interactive Calendar Grid --}}
  <div>
    <section class="card cal" style="padding:1.5rem">
      <div class="cal-head">
        <button class="btn ghost sm" id="prev" aria-label="Bulan sebelumnya" style="width:38px;height:38px;padding:0;border-radius:10px">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
        <h2 id="monthLabel" style="margin:0;font-size:1.3rem;font-weight:800">Kalender</h2>
        <button class="btn ghost sm" id="next" aria-label="Bulan berikutnya" style="width:38px;height:38px;padding:0;border-radius:10px">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
      </div>

      <div class="cal-sub">
        <button class="btn sm ghost" id="today">Ke Hari Ini</button>
        @auth
          @if($user->isAdmin() || $user->isGuru() || $user->isWaka())
            <a class="btn sm" href="{{ route('events.create') }}">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Ajukan Agenda
            </a>
          @endif
        @endauth
      </div>

      <div class="grid dow">
        <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span>
      </div>
      <div class="grid days" id="days" aria-live="polite"></div>
    </section>

    {{-- Selected Day's Agenda Details --}}
    <section class="card" style="margin-top:1.25rem">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem">
        <h2 id="dayTitle" style="margin:0;font-size:1.15rem">Agenda Terpilih</h2>
        <span class="badge" style="font-size:.75rem">Jadwal Ruangan</span>
      </div>
      <ul class="agenda" id="dayList"></ul>
    </section>
  </div>

  {{-- Right Column: Dark Action Card --}}
  <div>
    <div class="dark-action-banner">
      <span class="banner-badge">LAYANAN TERPADU</span>
      <h3 class="banner-title">Peminjaman Ruang &amp; Lapor Kerusakan</h3>
      <p class="banner-desc">
        Akses cepat kalender ruangan, ajukan peminjaman sarpras, dan laporkan kendala secara realtime langsung dengan kamera.
      </p>
      @auth
        @if($user->isAdmin() || $user->isGuru() || $user->isWaka())
          <a href="{{ route('events.create') }}" class="btn-banner-cta">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Ajukan Peminjaman
          </a>
        @endif
      @else
        <a href="{{ route('login') }}" class="btn-banner-cta">Masuk Sekarang</a>
      @endauth
    </div>

    <div class="card" style="margin-top:1.25rem">
      <h3 style="font-size:.95rem;margin-bottom:.5rem">Petunjuk Kalender</h3>
      <p class="muted" style="font-size:.82rem;line-height:1.6">
        • Klik pada tanggal untuk melihat rincian peminjaman ruang.<br>
        • Titik berwarna menandai ruangan yang dipesan.<br>
        • Agenda berwarna pucat masih berstatus <b>Diajukan</b> dan menunggu ACC Waka Sarpras.
      </p>
    </div>
  </div>
</div>
@endsection
