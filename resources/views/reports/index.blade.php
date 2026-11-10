@extends('layouts.app')

@push('head')
<style>
  /* Report Specific Styling */
  .report-period-selector {
    display: inline-flex;
    background: var(--c-surface-sub);
    padding: 4px;
    border-radius: var(--radius-pill);
    border: 1px solid var(--border);
    gap: 4px;
  }
  .report-period-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: .45rem 1.15rem;
    border-radius: var(--radius-pill);
    font-size: .82rem;
    font-weight: 700;
    color: var(--muted);
    text-decoration: none;
    transition: all .18s ease;
  }
  .report-period-btn:hover {
    color: var(--ink);
    background: var(--c-cream-soft);
  }
  .report-period-btn.active {
    background: var(--c-espresso);
    color: var(--c-cream);
    box-shadow: 0 2px 6px rgba(28, 22, 18, 0.18);
  }

  .report-filter-bar {
    background: var(--c-surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 1.1rem 1.35rem;
    margin-bottom: 1.5rem;
    box-shadow: var(--shadow-sm);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
  }
  .report-filter-form {
    display: flex;
    align-items: center;
    gap: .75rem;
    flex-wrap: wrap;
    margin: 0;
  }
  .report-actions {
    display: flex;
    align-items: center;
    gap: .65rem;
    flex-wrap: wrap;
  }

  .report-nav-stepper {
    display: inline-flex;
    align-items: center;
    background: var(--c-cream-soft);
    border: 1px solid var(--border);
    border-radius: var(--radius-pill);
    padding: 2px;
  }
  .report-nav-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: var(--radius-pill);
    color: var(--ink);
    text-decoration: none;
    transition: background .15s;
  }
  .report-nav-btn:hover {
    background: var(--c-cream);
  }
  .report-nav-today {
    padding: 0 .75rem;
    font-size: .78rem;
    font-weight: 700;
    color: var(--ink-secondary);
    text-decoration: none;
  }
  .report-nav-today:hover {
    color: var(--accent);
  }

  /* Report Inputs */
  .report-filter-input {
    padding: .45rem .65rem;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border);
    font-size: .85rem;
    background: var(--c-surface-sub);
    color: var(--ink);
    transition: border-color .15s ease, background .15s ease;
  }
  .report-filter-input:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 2px rgba(184, 130, 79, 0.15);
  }

  /* Report Table Styling */
  .report-table-card {
    background: var(--c-surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    margin-bottom: 1.75rem;
  }
  .report-table-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border-light);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: .75rem;
    background: var(--c-surface-sub);
  }
  .report-table-title {
    display: flex;
    align-items: center;
    gap: .65rem;
    margin: 0;
    font-size: 1.05rem;
    font-weight: 800;
    color: var(--ink);
  }
  .report-table-sub {
    font-size: .8rem;
    color: var(--muted);
    margin: .15rem 0 0;
  }
  .table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }
  .report-table {
    width: 100%;
    border-collapse: collapse;
    font-size: .88rem;
    text-align: left;
  }
  .report-table th {
    background: var(--c-cream-soft);
    color: var(--ink-secondary);
    font-size: .75rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .04em;
    padding: .85rem 1rem;
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
  }
  .report-table td {
    padding: .95rem 1rem;
    border-bottom: 1px solid var(--border-light);
    color: var(--ink);
    vertical-align: middle;
  }
  .report-table tbody tr:hover {
    background: rgba(223, 217, 206, 0.15);
  }
  .report-table tbody tr:last-child td {
    border-bottom: 0;
  }

  .stat-mini-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: .2rem .55rem;
    border-radius: 8px;
    font-size: .74rem;
    font-weight: 700;
    white-space: nowrap;
    line-height: 1.2;
  }
  .stat-mini-pill.ok {
    background: var(--ok-bg);
    color: var(--ok-txt);
  }
  .stat-mini-pill.warn {
    background: var(--warn-bg);
    color: var(--warn-txt);
  }
  .stat-mini-pill.bad {
    background: var(--bad-bg);
    color: var(--bad-txt);
  }
  .stat-mini-pill.neutral {
    background: var(--c-cream-soft);
    color: var(--ink-secondary);
    border: 1px solid var(--border);
  }

  .room-pill-chip {
    display: inline-block;
    padding: 4px 10px;
    background: var(--c-cream-soft);
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: .78rem;
    font-weight: 700;
    color: var(--ink);
    line-height: 1.35;
    max-width: 240px;
    word-break: break-word;
  }

  .report-tab-toggle {
    display: flex;
    gap: .4rem;
    border-bottom: 2px solid var(--border-light);
    margin-bottom: 1.5rem;
    padding-bottom: 2px;
  }
  .report-tab-item {
    background: transparent;
    border: 0;
    padding: .65rem 1.15rem;
    font-size: .88rem;
    font-weight: 700;
    color: var(--muted);
    cursor: pointer;
    border-radius: 8px 8px 0 0;
    position: relative;
    transition: all .15s ease;
  }
  .report-tab-item:hover {
    color: var(--ink);
    background: var(--c-cream-soft);
  }
  .report-tab-item.active {
    color: var(--ink);
  }
  .report-tab-item.active::after {
    content: '';
    position: absolute;
    bottom: -4px;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--accent);
    border-radius: 3px 3px 0 0;
  }

  @media (max-width: 900px) {
    .report-filter-bar {
      flex-direction: column;
      align-items: stretch;
    }
    .report-filter-form, .report-actions {
      width: 100%;
    }
  }
</style>
@endpush

@section('content')
@php
  $user = auth()->user();
@endphp

{{-- ═══════════════ TOP HEADER & PERIOD SWITCHER ═══════════════ --}}
<div class="page-head" style="margin-bottom:1.25rem">
  <div>
    <div style="display:flex;align-items:center;gap:.65rem;flex-wrap:wrap">
      <h2 style="margin:0;font-size:1.35rem">Laporan &amp; Rekapitulasi Sarpras</h2>
      <span class="hero-stat-badge">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        Khusus Waka &amp; Admin
      </span>
    </div>
    <p class="muted" style="margin:.25rem 0 0;font-size:.86rem">
      Pantau rekapan harian, mingguan, dan bulanan penggunaan ruangan serta status tindak lanjut perbaikan fasilitas sekolah.
    </p>
  </div>

  {{-- Period Tabs --}}
  <div class="report-period-selector" role="tablist" aria-label="Pilihan Periode Laporan">
    <a href="{{ route('reports.index', ['period' => 'daily', 'room_id' => $roomId]) }}"
       class="report-period-btn {{ $period === 'daily' ? 'active' : '' }}">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      Harian
    </a>
    <a href="{{ route('reports.index', ['period' => 'weekly', 'room_id' => $roomId]) }}"
       class="report-period-btn {{ $period === 'weekly' ? 'active' : '' }}">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="14" x2="8.01" y2="14"/><line x1="12" y1="14" x2="12.01" y2="14"/><line x1="16" y1="14" x2="16.01" y2="14"/></svg>
      Mingguan
    </a>
    <a href="{{ route('reports.index', ['period' => 'monthly', 'room_id' => $roomId]) }}"
       class="report-period-btn {{ $period === 'monthly' ? 'active' : '' }}">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
      Bulanan
    </a>
  </div>
</div>

{{-- ═══════════════ TOP STATS BAR (Image 2 Replica) ═══════════════ --}}
<div class="dashboard-top-grid">
  {{-- Hero Summary Card --}}
  <div class="hero-stat-card">
    <div class="hero-stat-header">
      <span class="hero-stat-title">Periode Laporan</span>
      <div class="report-nav-stepper">
        <a href="{{ route('reports.index', $navQueryPrev) }}" class="report-nav-btn" title="Sebelumnya">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        </a>
        <a href="{{ route('reports.index', $navQueryCurrent) }}" class="report-nav-today">
          {{ $period === 'daily' ? 'Hari Ini' : ($period === 'weekly' ? 'Minggu Ini' : 'Bulan Ini') }}
        </a>
        <a href="{{ route('reports.index', $navQueryNext) }}" class="report-nav-btn" title="Berikutnya">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
      </div>
    </div>

    <div class="hero-stat-value" style="font-size:1.65rem;line-height:1.25">
      {{ $periodLabel }}
    </div>
    <div class="hero-stat-sub">
      {{ $periodSub }} @if($selectedRoom) · Ruang: <b>{{ $selectedRoom->room_name }}</b> @endif
    </div>

    <div class="hero-stat-pills" style="margin-top:auto">
      <span class="stat-mini-pill neutral">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/></svg>
        {{ $cntEventsTotal }} Total Agenda
      </span>
      <span class="stat-mini-pill neutral">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
        {{ $cntComplaintsTotal }} Total Aduan
      </span>
      <span class="stat-mini-pill ok">
        {{ $repairRate }}% Penyelesaian
      </span>
    </div>
  </div>

  {{-- 3 Asset / Status Cards Stack --}}
  <div class="asset-cards-row">
    {{-- Card 1: Agenda Disetujui & Review --}}
    <div class="asset-card purple">
      <div class="asset-card-top">
        <div>
          <div class="asset-card-value">{{ $cntEventsApproved }} Disetujui</div>
          <div class="asset-card-sub">Agenda Ruangan Aktif</div>
        </div>
      </div>
      <div class="asset-card-bottom">
        <div style="font-size:.78rem;color:var(--ink-secondary)">
          <b>{{ $cntEventsPending }}</b> menunggu · <b>{{ $cntEventsRejected }}</b> ditolak
        </div>
        <span class="asset-delta-tag pos">Jadwal</span>
      </div>
    </div>

    {{-- Card 2: Perbaikan Selesai --}}
    <div class="asset-card green">
      <div class="asset-card-top">
        <div>
          <div class="asset-card-value">{{ $cntRepairDone }} Perbaikan</div>
          <div class="asset-card-sub">Fasilitas Selesai Diperbaiki</div>
        </div>
      </div>
      <div class="asset-card-bottom">
        <div style="font-size:.78rem;color:var(--ok-txt);font-weight:700">
          Tingkat Selesai: {{ $repairRate }}%
        </div>
        <span class="asset-delta-tag pos">Optimal</span>
      </div>
    </div>

    {{-- Card 3: Kerusakan Dalam Proses --}}
    <div class="asset-card gold">
      <div class="asset-card-top">
        <div>
          <div class="asset-card-value">{{ $cntRepairPending + $cntRepairWaiting }} Dalam Proses</div>
          <div class="asset-card-sub">Antrean / Menunggu Part</div>
        </div>
      </div>
      <div class="asset-card-bottom">
        <div style="font-size:.78rem;color:var(--warn-txt)">
          <b>{{ $cntRepairPending }}</b> belum · <b>{{ $cntRepairWaiting }}</b> spare part
        </div>
        <span class="asset-delta-tag neutral">Pemeliharaan</span>
      </div>
    </div>
  </div>
</div>

{{-- ═══════════════ FILTER & ACTIONS TOOLBAR ═══════════════ --}}
<div class="report-filter-bar">
  <form method="get" action="{{ route('reports.index') }}" class="report-filter-form">
    <input type="hidden" name="period" value="{{ $period }}">

    {{-- Date picker based on period --}}
    @if($period === 'daily')
      <div style="display:flex;align-items:center;gap:.4rem">
        <label for="filterDate" style="font-size:.8rem;font-weight:700;color:var(--ink-secondary)">Tanggal:</label>
        <input type="date" id="filterDate" name="date" value="{{ $selectedDate }}" class="report-filter-input">
      </div>
    @elseif($period === 'weekly')
      <div style="display:flex;align-items:center;gap:.4rem">
        <label for="filterWeekDate" style="font-size:.8rem;font-weight:700;color:var(--ink-secondary)">Pilih Minggu (Tanggal):</label>
        <input type="date" id="filterWeekDate" name="date" value="{{ $selectedDate }}" class="report-filter-input">
      </div>
    @else
      <div style="display:flex;align-items:center;gap:.4rem">
        <label for="filterMonth" style="font-size:.8rem;font-weight:700;color:var(--ink-secondary)">Bulan:</label>
        <select id="filterMonth" name="month" class="report-filter-input">
          @foreach([1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'] as $mNum => $mName)
            <option value="{{ $mNum }}" {{ $selectedMonth == $mNum ? 'selected' : '' }}>{{ $mName }}</option>
          @endforeach
        </select>
        <label for="filterYear" style="font-size:.8rem;font-weight:700;color:var(--ink-secondary);margin-left:.25rem">Tahun:</label>
        <select id="filterYear" name="year" class="report-filter-input">
          @for($y = now()->year - 2; $y <= now()->year + 2; $y++)
            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
          @endfor
        </select>
      </div>
    @endif

    {{-- Room Filter --}}
    <div style="display:flex;align-items:center;gap:.4rem">
      <label for="filterRoom" style="font-size:.8rem;font-weight:700;color:var(--ink-secondary)">Ruangan:</label>
      <select id="filterRoom" name="room_id" class="report-filter-input">
        <option value="0">Semua Ruangan</option>
        @foreach($rooms as $r)
          <option value="{{ $r->id }}" {{ $roomId == $r->id ? 'selected' : '' }}>{{ $r->room_name }}</option>
        @endforeach
      </select>
    </div>

    <button type="submit" class="btn sm" style="padding:.45rem .85rem">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      Terapkan
    </button>
  </form>

  {{-- Export & Print Buttons --}}
  <div class="report-actions">
    <a href="{{ route('reports.print', request()->query()) }}" target="_blank" class="btn sm" title="Buka format cetak resmi">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
      Cetak Laporan
    </a>
    <a href="{{ route('reports.export', request()->query()) }}" class="btn sm ghost" title="Unduh rekapitulasi ke format CSV/Excel">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
      Ekspor CSV
    </a>
  </div>
</div>

{{-- ═══════════════ TAB SWITCHER (All / Agenda / Pengaduan) ═══════════════ --}}
<div class="report-tab-toggle" role="tablist">
  <button type="button" class="report-tab-item active" onclick="switchReportSection('all', this)">
    Semua Rekapitulasi
  </button>
  <button type="button" class="report-tab-item" onclick="switchReportSection('events', this)">
    Agenda &amp; Peminjaman ({{ $cntEventsTotal }})
  </button>
  <button type="button" class="report-tab-item" onclick="switchReportSection('complaints', this)">
    Pengaduan Kerusakan ({{ $cntComplaintsTotal }})
  </button>
</div>

{{-- ═══════════════ SECTION 1: EVENTS / ROOM BOOKINGS ═══════════════ --}}
<div id="sectionEvents" class="report-section-block">
  <div class="report-table-card">
    <div class="report-table-header">
      <div>
        <h3 class="report-table-title">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          Rekapitulasi Peminjaman &amp; Penggunaan Ruang
        </h3>
        <p class="report-table-sub">Total {{ $cntEventsTotal }} kegiatan peminjaman pada periode {{ $periodLabel }}</p>
      </div>

      {{-- Top Rooms Chips --}}
      @if($topRooms->isNotEmpty())
        <div style="display:flex;align-items:center;gap:.35rem;flex-wrap:wrap">
          <span style="font-size:.75rem;font-weight:700;color:var(--muted)">Ruang Terpopuler:</span>
          @foreach($topRooms as $tr)
            <span class="room-pill-chip">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 4H3v16h18V9z"/></svg>
              {{ $tr['room_name'] }} ({{ $tr['count'] }}x)
            </span>
          @endforeach
        </div>
      @endif
    </div>

    @if($events->isEmpty())
      <div style="padding:2.5rem 1.5rem;text-align:center">
        <div class="item-icon-squircle" style="margin:0 auto .75rem;width:44px;height:44px;background:var(--c-cream-soft);color:var(--muted)">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/></svg>
        </div>
        <h4 style="margin:0;font-size:1rem;color:var(--ink)">Tidak Ada Peminjaman Ruang</h4>
        <p class="muted" style="margin:.25rem 0 0;font-size:.82rem">Tidak tercatat agenda atau peminjaman ruang pada periode yang dipilih.</p>
      </div>
    @else
      <div class="table-responsive">
        <table class="report-table">
          <thead>
            <tr>
              <th style="width:40px;text-align:center">No</th>
              <th>Tanggal &amp; Waktu</th>
              <th>Nama Kegiatan / Agenda</th>
              <th>Ruangan</th>
              <th>Peminjam / PIC</th>
              <th>Status</th>
              <th>Disetujui Oleh</th>
              <th>Keterangan / Catatan</th>
            </tr>
          </thead>
          <tbody>
            @foreach($events as $idx => $ev)
              @php
                $statusCls = ['Diajukan'=>'warn', 'Disetujui'=>'ok', 'Ditolak'=>'bad'][$ev->status] ?? 'neutral';
              @endphp
              <tr>
                <td style="text-align:center;font-weight:700;color:var(--muted)">{{ $idx + 1 }}</td>
                <td>
                  <div style="font-weight:700">{{ $ev->date->translatedFormat('d M Y') }}</div>
                  <div class="muted" style="font-size:.76rem">
                    {{ substr($ev->start_time, 0, 5) }} – {{ substr($ev->end_time, 0, 5) }} WIB
                  </div>
                </td>
                <td>
                  <div style="font-weight:700;color:var(--ink)">{{ $ev->event_name }}</div>
                </td>
                <td>
                  <span class="room-pill-chip">
                    {{ $ev->room?->room_name ?? '-' }}
                  </span>
                </td>
                <td>
                  <div style="font-weight:600">{{ $ev->requester?->fullname ?? $ev->requester?->username ?? '-' }}</div>
                  <div class="muted" style="font-size:.74rem">{{ $ev->requester?->phone_number ?? '' }}</div>
                </td>
                <td>
                  <span class="stat-mini-pill {{ $statusCls }}">{{ $ev->status }}</span>
                </td>
                <td>
                  <span style="font-size:.82rem;color:var(--ink-secondary)">
                    {{ $ev->approver?->fullname ?? ($ev->status === 'Diajukan' ? 'Menunggu' : '-') }}
                  </span>
                </td>
                <td style="font-size:.82rem;color:var(--ink-secondary);max-width:240px">
                  @if($ev->move_reason)
                    <span style="color:var(--accent);font-weight:600">Dipindah:</span> {{ $ev->move_reason }}
                  @elseif($ev->reject_reason)
                    <span style="color:var(--bad);font-weight:600">Ditolak:</span> {{ $ev->reject_reason }}
                  @else
                    <span class="muted">-</span>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>

{{-- ═══════════════ SECTION 2: COMPLAINTS / REPAIRS ═══════════════ --}}
<div id="sectionComplaints" class="report-section-block">
  <div class="report-table-card">
    <div class="report-table-header">
      <div>
        <h3 class="report-table-title">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
          Rekapitulasi Pengaduan &amp; Pemeliharaan Fasilitas
        </h3>
        <p class="report-table-sub">Total {{ $cntComplaintsTotal }} aduan kerusakan pada periode {{ $periodLabel }}</p>
      </div>

      {{-- Repair Status Counts --}}
      <div style="display:flex;align-items:center;gap:.35rem;flex-wrap:wrap">
        <span class="stat-mini-pill ok">
          ✓ {{ $cntRepairDone }} Selesai
        </span>
        <span class="stat-mini-pill warn">
          {{ $cntRepairWaiting }} Menunggu Part
        </span>
        <span class="stat-mini-pill bad">
          ✕ {{ $cntRepairPending }} Belum Diperbaiki
        </span>
      </div>
    </div>

    @if($complaints->isEmpty())
      <div style="padding:2.5rem 1.5rem;text-align:center">
        <div class="item-icon-squircle" style="margin:0 auto .75rem;width:44px;height:44px;background:var(--c-cream-soft);color:var(--muted)">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
        </div>
        <h4 style="margin:0;font-size:1rem;color:var(--ink)">Tidak Ada Pengaduan Kerusakan</h4>
        <p class="muted" style="margin:.25rem 0 0;font-size:.82rem">Kondisi seluruh sarana dan prasarana sekolah tercatat dalam kondisi baik tanpa keluhan pada periode ini.</p>
      </div>
    @else
      <div class="table-responsive">
        <table class="report-table">
          <thead>
            <tr>
              <th style="width:40px;text-align:center">No</th>
              <th>Tanggal Lapor</th>
              <th>Ruangan</th>
              <th>Pelapor</th>
              <th>Deskripsi Masalah / Kerusakan</th>
              <th>Verifikasi</th>
              <th>Status Perbaikan</th>
              <th>Teknisi / PIC Penangan</th>
              <th>Catatan Perbaikan</th>
            </tr>
          </thead>
          <tbody>
            @foreach($complaints as $idx => $comp)
              @php
                $vCls = ['Diajukan'=>'warn', 'Diterima'=>'ok', 'Ditolak'=>'bad'][$comp->status] ?? 'neutral';
                $rCls = ['Belum Diperbaiki'=>'bad', 'Menunggu Spare Part'=>'warn', 'Sudah Diperbaiki'=>'ok'][$comp->status_repair] ?? 'neutral';
              @endphp
              <tr>
                <td style="text-align:center;font-weight:700;color:var(--muted)">{{ $idx + 1 }}</td>
                <td>
                  <div style="font-weight:700">{{ \Illuminate\Support\Carbon::parse($comp->reported_at)->translatedFormat('d M Y') }}</div>
                  <div class="muted" style="font-size:.76rem">{{ \Illuminate\Support\Carbon::parse($comp->reported_at)->format('H:i') }} WIB</div>
                </td>
                <td>
                  <span class="room-pill-chip">
                    {{ $comp->room?->room_name ?? '-' }}
                  </span>
                </td>
                <td>
                  <div style="font-weight:600">{{ $comp->reporter?->fullname ?? $comp->reporter?->username ?? '-' }}</div>
                  <div class="muted" style="font-size:.74rem">{{ $comp->reporter?->role?->role_name ?? '' }}</div>
                </td>
                <td style="max-width:280px">
                  <div style="color:var(--ink);font-weight:500;line-height:1.4">{{ $comp->description }}</div>
                  @if($comp->photo_path)
                    <div style="margin-top:4px">
                      <a href="{{ asset('storage/' . $comp->photo_path) }}" target="_blank" style="font-size:.75rem;color:var(--accent);display:inline-flex;align-items:center;gap:3px">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        Lihat Foto
                      </a>
                    </div>
                  @endif
                </td>
                <td>
                  <span class="stat-mini-pill {{ $vCls }}">{{ $comp->status }}</span>
                </td>
                <td>
                  <span class="stat-mini-pill {{ $rCls }}">{{ $comp->status_repair }}</span>
                </td>
                <td>
                  <span style="font-size:.82rem;color:var(--ink-secondary)">
                    {{ $comp->handler?->fullname ?? '-' }}
                  </span>
                </td>
                <td style="font-size:.82rem;color:var(--ink-secondary);max-width:220px">
                  @if($comp->repair_note)
                    {{ $comp->repair_note }}
                  @elseif($comp->reject_reason)
                    <span style="color:var(--bad);font-weight:600">Alasan Tolak:</span> {{ $comp->reject_reason }}
                  @else
                    <span class="muted">-</span>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>

<script>
  function switchReportSection(sec, btn) {
    document.querySelectorAll('.report-tab-item').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const evSec = document.getElementById('sectionEvents');
    const compSec = document.getElementById('sectionComplaints');

    if (sec === 'events') {
      evSec.style.display = 'block';
      compSec.style.display = 'none';
    } else if (sec === 'complaints') {
      evSec.style.display = 'none';
      compSec.style.display = 'block';
    } else {
      evSec.style.display = 'block';
      compSec.style.display = 'block';
    }
  }
</script>
@endsection
