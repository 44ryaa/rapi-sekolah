<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Cetak Laporan Sarpras · {{ $periodLabel }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Times+New+Roman&display=swap" rel="stylesheet">

<style>
  *, *::before, *::after { box-sizing: border-box; }
  body {
    margin: 0;
    padding: 20px;
    font-family: 'Plus Jakarta Sans', Arial, sans-serif;
    color: #1a1a1a;
    background: #525659;
    font-size: 12px;
    line-height: 1.45;
  }

  /* Paper sheet container */
  .sheet {
    background: #ffffff;
    max-width: 900px;
    margin: 0 auto;
    padding: 30px 40px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.18);
    border-radius: 4px;
    position: relative;
  }

  /* Non-print toolbar */
  .toolbar {
    position: sticky;
    top: 15px;
    max-width: 900px;
    margin: 0 auto 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #1c1612;
    padding: 10px 18px;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.25);
    z-index: 1000;
  }
  .toolbar-left {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #dfd9ce;
    font-size: 13px;
    font-weight: 700;
  }
  .toolbar-actions {
    display: flex;
    gap: 8px;
  }
  .btn-print {
    background: #b8824f;
    color: #fff;
    border: 0;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background .15s;
  }
  .btn-print:hover { background: #9a693a; }
  .btn-close {
    background: rgba(223, 217, 206, 0.15);
    color: #dfd9ce;
    border: 0;
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }
  .btn-close:hover { background: rgba(223, 217, 206, 0.25); }

  /* Official Indonesian School Letterhead (Kop Surat) */
  .kop {
    display: flex;
    align-items: center;
    gap: 16px;
    padding-bottom: 12px;
    border-bottom: 3px double #000;
    margin-bottom: 18px;
  }
  .kop-logo {
    width: 70px;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #1c1612;
    color: #dfd9ce;
    border-radius: 12px;
    flex-shrink: 0;
  }
  .kop-text {
    flex: 1;
    text-align: center;
  }
  .kop-text h4 {
    margin: 0;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
  }
  .kop-text h2 {
    margin: 2px 0;
    font-size: 18px;
    font-weight: 800;
    letter-spacing: .04em;
    text-transform: uppercase;
  }
  .kop-text h3 {
    margin: 0;
    font-size: 13px;
    font-weight: 700;
    color: #333;
    letter-spacing: .02em;
  }
  .kop-text p {
    margin: 2px 0 0;
    font-size: 10px;
    color: #555;
  }

  /* Document Title */
  .doc-title {
    text-align: center;
    margin-bottom: 18px;
  }
  .doc-title h1 {
    margin: 0;
    font-size: 15px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .05em;
    text-decoration: underline;
  }
  .doc-title .doc-subtitle {
    margin: 3px 0 0;
    font-size: 11px;
    font-weight: 600;
    color: #333;
  }

  /* Metadata Info */
  .meta-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px 16px;
    background: #f8f8f8;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 8px 14px;
    font-size: 11px;
    margin-bottom: 16px;
  }
  .meta-item { display: flex; gap: 8px; }
  .meta-label { font-weight: 700; color: #555; width: 110px; flex-shrink: 0; }
  .meta-val { font-weight: 600; color: #111; }

  /* Summary Table */
  .summary-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 18px;
    font-size: 11px;
  }
  .summary-table th, .summary-table td {
    border: 1px solid #ccc;
    padding: 6px 10px;
    text-align: center;
  }
  .summary-table th {
    background: #eaeaea;
    font-weight: 700;
    font-size: 10.5px;
  }

  /* Section Title */
  .section-heading {
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    margin: 16px 0 6px;
    display: flex;
    align-items: center;
    gap: 6px;
    border-left: 3px solid #b8824f;
    padding-left: 8px;
  }

  /* Data Table */
  .data-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 16px;
    font-size: 10.5px;
  }
  .data-table th, .data-table td {
    border: 1px solid #ccc;
    padding: 6px 8px;
    vertical-align: middle;
  }
  .data-table th {
    background: #f1f1f1;
    font-weight: 700;
    text-align: left;
    white-space: nowrap;
  }
  .data-table td.center, .data-table th.center { text-align: center; }

  /* Badges */
  .badge-print {
    display: inline-block;
    padding: 1px 6px;
    border-radius: 4px;
    font-size: 9.5px;
    font-weight: 700;
    border: 1px solid #ccc;
  }
  .badge-print.ok { background: #e9f5ed; color: #124d2d; border-color: #cae7ce; }
  .badge-print.warn { background: #fdf4e6; color: #6e4408; border-color: #ede0bf; }
  .badge-print.bad { background: #fdeeed; color: #7a1616; border-color: #f7c3c3; }

  /* Signatures */
  .signature-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    margin-top: 30px;
    font-size: 11px;
    page-break-inside: avoid;
  }
  .sig-col {
    text-align: center;
    padding: 0 20px;
  }
  .sig-title { font-weight: 600; color: #444; }
  .sig-space { height: 60px; }
  .sig-name { font-weight: 800; text-decoration: underline; color: #000; }
  .sig-nip { font-size: 10px; color: #555; }

  /* Print Media Query */
  @media print {
    body {
      background: #ffffff !important;
      padding: 0 !important;
      font-size: 11px !important;
    }
    .sheet {
      box-shadow: none !important;
      padding: 0 !important;
      max-width: 100% !important;
      border-radius: 0 !important;
    }
    .toolbar { display: none !important; }
    .data-table tr { page-break-inside: avoid; }
    @page {
      size: A4 portrait;
      margin: 12mm 15mm 15mm 15mm;
    }
  }
</style>
</head>
<body>

{{-- Non-print Floating Toolbar --}}
<div class="toolbar no-print">
  <div class="toolbar-left">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
    <span>Pratinjau Cetak Laporan Sarpras</span>
  </div>
  <div class="toolbar-actions">
    <button type="button" class="btn-print" onclick="window.print()">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
      Cetak Sekarang (Print / PDF)
    </button>
    <a href="{{ route('reports.index', request()->query()) }}" class="btn-close">
      Tutup
    </a>
  </div>
</div>

<div class="sheet">
  {{-- ═══ KOP SURAT RESMI SEKOLAH ═══ --}}
  <div class="kop">
    <div class="kop-logo" style="background:transparent;width:75px;height:75px;">
      <img src="{{ asset('assets/img/logo-smk.png') }}" alt="Logo SMK N 1 Bantul" style="width:100%;height:100%;object-fit:contain;">
    </div>
    <div class="kop-text">
      <h4>PEMERINTAH DAERAH DAERAH ISTIMEWA YOGYAKARTA · DINAS PENDIDIKAN, PEMUDA, DAN OLAHRAGA</h4>
      <h2>SMK NEGERI 1 BANTUL</h2>
      <h3>SISTEM INFORMASI SARANA &amp; PRASARANA SEKOLAH (RAPI-SEKOLAH)</h3>
      <p>Jl. Parangtritis No.KM.11, Sabdodadi, Kec. Bantul, Kabupaten Bantul, Daerah Istimewa Yogyakarta 55715</p>
    </div>
  </div>

  {{-- ═══ JUDUL LAPORAN ═══ --}}
  <div class="doc-title">
    <h1>LAPORAN REKAPITULASI SARANA DAN PRASARANA</h1>
    <div class="doc-subtitle">Tipe: <b>{{ strtoupper($period) }}</b> · Periode: <b>{{ $periodLabel }}</b></div>
  </div>

  {{-- ═══ METADATA ═══ --}}
  <div class="meta-grid">
    <div class="meta-item">
      <span class="meta-label">Rentang Waktu:</span>
      <span class="meta-val">{{ $startDate->translatedFormat('d M Y') }} s/d {{ $endDate->translatedFormat('d M Y') }}</span>
    </div>
    <div class="meta-item">
      <span class="meta-label">Filter Ruangan:</span>
      <span class="meta-val">{{ $selectedRoom ? $selectedRoom->room_name : 'Semua Ruangan' }}</span>
    </div>
    <div class="meta-item">
      <span class="meta-label">Tanggal Cetak:</span>
      <span class="meta-val">{{ now()->translatedFormat('l, d F Y - H:i') }} WIB</span>
    </div>
    <div class="meta-item">
      <span class="meta-label">Dicetak Oleh:</span>
      <span class="meta-val">{{ auth()->user()?->fullname ?? auth()->user()?->username }} ({{ auth()->user()?->role?->role_name ?? 'User' }})</span>
    </div>
  </div>

  {{-- ═══ RINGKASAN EKSEKUTIF METRIK ═══ --}}
  <div class="section-heading">I. Ringkasan Eksekutif Metrik Sarpras</div>
  <table class="summary-table">
    <thead>
      <tr>
        <th colspan="4" style="background:#e0d3ed;color:#4e3966">Agenda &amp; Peminjaman Ruangan</th>
        <th colspan="4" style="background:#cae7ce;color:#225930">Pengaduan Kerusakan &amp; Pemeliharaan</th>
      </tr>
      <tr>
        <th>Total Agenda</th>
        <th>Disetujui</th>
        <th>Menunggu</th>
        <th>Ditolak</th>
        <th>Total Aduan</th>
        <th>Selesai</th>
        <th>Menunggu Part</th>
        <th>Belum Perbaikan</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><b>{{ $cntEventsTotal }}</b></td>
        <td style="color:#124d2d;font-weight:700">{{ $cntEventsApproved }}</td>
        <td style="color:#6e4408;font-weight:700">{{ $cntEventsPending }}</td>
        <td style="color:#7a1616;font-weight:700">{{ $cntEventsRejected }}</td>
        <td><b>{{ $cntComplaintsTotal }}</b></td>
        <td style="color:#124d2d;font-weight:700">{{ $cntRepairDone }}</td>
        <td style="color:#6e4408;font-weight:700">{{ $cntRepairWaiting }}</td>
        <td style="color:#7a1616;font-weight:700">{{ $cntRepairPending }}</td>
      </tr>
    </tbody>
  </table>

  {{-- ═══ TABEL AGENDA & PEMINJAMAN RUANG ═══ --}}
  <div class="section-heading">II. Rekapitulasi Peminjaman &amp; Penggunaan Ruang ({{ $cntEventsTotal }} Agenda)</div>
  <table class="data-table">
    <thead>
      <tr>
        <th class="center" style="width:25px">No</th>
        <th>Tanggal &amp; Waktu</th>
        <th>Nama Acara / Kegiatan</th>
        <th>Ruangan</th>
        <th>Peminjam / PIC</th>
        <th class="center">Status</th>
        <th>Persetujuan</th>
        <th>Catatan / Alasan</th>
      </tr>
    </thead>
    <tbody>
      @forelse($events as $idx => $ev)
        @php
          $cls = ['Disetujui'=>'ok', 'Diajukan'=>'warn', 'Ditolak'=>'bad'][$ev->status] ?? '';
        @endphp
        <tr>
          <td class="center">{{ $idx + 1 }}</td>
          <td>{{ $ev->date->translatedFormat('d/m/Y') }}<br><small style="color:#666">{{ substr($ev->start_time,0,5) }}–{{ substr($ev->end_time,0,5) }}</small></td>
          <td><b>{{ $ev->event_name }}</b></td>
          <td>{{ $ev->room?->room_name ?? '-' }}</td>
          <td>{{ $ev->requester?->fullname ?? $ev->requester?->username ?? '-' }}</td>
          <td class="center"><span class="badge-print {{ $cls }}">{{ $ev->status }}</span></td>
          <td>{{ $ev->approver?->fullname ?? '-' }}</td>
          <td>{{ $ev->move_reason ?: ($ev->reject_reason ?: '-') }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="8" class="center" style="color:#777;padding:12px">Tidak ada data peminjaman ruangan pada periode ini.</td>
        </tr>
      @endforelse
    </tbody>
  </table>

  {{-- ═══ TABEL PENGADUAN & PERBAIKAN KERUSAKAN ═══ --}}
  <div class="section-heading">III. Rekapitulasi Pengaduan Kerusakan &amp; Pemeliharaan ({{ $cntComplaintsTotal }} Aduan)</div>
  <table class="data-table">
    <thead>
      <tr>
        <th class="center" style="width:25px">No</th>
        <th>Tanggal Lapor</th>
        <th>Ruangan</th>
        <th>Pelapor</th>
        <th>Deskripsi Masalah</th>
        <th class="center">Verifikasi</th>
        <th class="center">Status Perbaikan</th>
        <th>Teknisi / Handler</th>
        <th>Catatan Perbaikan</th>
      </tr>
    </thead>
    <tbody>
      @forelse($complaints as $idx => $comp)
        @php
          $vCls = ['Diterima'=>'ok', 'Diajukan'=>'warn', 'Ditolak'=>'bad'][$comp->status] ?? '';
          $rCls = ['Sudah Diperbaiki'=>'ok', 'Menunggu Spare Part'=>'warn', 'Belum Diperbaiki'=>'bad'][$comp->status_repair] ?? '';
        @endphp
        <tr>
          <td class="center">{{ $idx + 1 }}</td>
          <td>{{ \Illuminate\Support\Carbon::parse($comp->reported_at)->translatedFormat('d/m/Y H:i') }}</td>
          <td>{{ $comp->room?->room_name ?? '-' }}</td>
          <td>{{ $comp->reporter?->fullname ?? $comp->reporter?->username ?? '-' }}</td>
          <td>{{ $comp->description }}</td>
          <td class="center"><span class="badge-print {{ $vCls }}">{{ $comp->status }}</span></td>
          <td class="center"><span class="badge-print {{ $rCls }}">{{ $comp->status_repair }}</span></td>
          <td>{{ $comp->handler?->fullname ?? '-' }}</td>
          <td>{{ $comp->repair_note ?: ($comp->reject_reason ?: '-') }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="9" class="center" style="color:#777;padding:12px">Tidak ada data aduan kerusakan fasilitas pada periode ini.</td>
        </tr>
      @endforelse
    </tbody>
  </table>

  {{-- ═══ LEMBAR PENGESAHAN / TANDA TANGAN RESMI ═══ --}}
  <div class="signature-grid">
    <div class="sig-col">
      <div class="sig-title">Petugas / Administrator Sarpras,</div>
      <div class="sig-space"></div>
      <div class="sig-name">{{ auth()->user()?->fullname ?? 'Administrator Sarpras' }}</div>
      <div class="sig-nip">NIP. {{ auth()->user()?->phone_number ? 'ID: ' . auth()->user()->id : '-' }}</div>
    </div>
    <div class="sig-col">
      <div class="sig-title">Mengetahui,<br>Wakil Kepala Sekolah Bidang Sarpras</div>
      <div class="sig-space"></div>
      <div class="sig-name">Drs. Hendra Permana, M.Pd.</div>
      <div class="sig-nip">NIP. 19780514 200501 1 008</div>
    </div>
  </div>
</div>

</body>
</html>
