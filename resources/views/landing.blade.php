<!doctype html>
<html lang="id" data-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>RAPI-Sekolah &middot; Sistem Manajemen Sarana dan Prasarana Terpadu</title>
  <link rel="icon" type="image/png" href="{{ asset('assets/img/logo-smk.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script>
    (function() {
      const saved = localStorage.getItem('theme');
      // Default to signature luxury dark theme unless explicitly set by user
      document.documentElement.setAttribute('data-theme', saved || 'dark');
    })();
  </script>
  <style>
    /* ── Palette: #000000 | #1F150C | #412D15 | #E1DCC9 ── */
    :root, [data-theme="dark"] {
      --bg: #000000;
      --bg-subtle: #0d0a07;
      --surface: #1F150C;
      --surface-border: rgba(225, 220, 201, 0.12);
      --surface-hover: #291d12;
      --border-strong: #412D15;
      --primary: #E1DCC9;
      --primary-hover: #ffffff;
      --primary-text: #1F150C;
      --primary-soft: rgba(225, 220, 201, 0.1);
      --text: #F5F2EA;
      --text-muted: #A89F91;
      --accent: #412D15;
      --accent-light: #5A4022;
      --badge-bg: rgba(65, 45, 21, 0.55);
      --badge-border: #412D15;
      --badge-text: #E1DCC9;
      --nav-bg: rgba(0, 0, 0, 0.85);
      --card-shadow: 0 4px 24px rgba(0, 0, 0, 0.6);
      --brand-grad: linear-gradient(135deg, #E1DCC9, #412D15);
      --brand-icon-color: #1F150C;
    }

    [data-theme="light"] {
      --bg: #F8F6F0;
      --bg-subtle: #ECE6D8;
      --surface: #FFFFFF;
      --surface-border: rgba(65, 45, 21, 0.14);
      --surface-hover: #F6F3EB;
      --border-strong: #412D15;
      --primary: #1F150C;
      --primary-hover: #000000;
      --primary-text: #E1DCC9;
      --primary-soft: rgba(31, 21, 12, 0.08);
      --text: #1F150C;
      --text-muted: #6B5947;
      --accent: #412D15;
      --accent-light: #5A4022;
      --badge-bg: rgba(225, 220, 201, 0.65);
      --badge-border: rgba(65, 45, 21, 0.25);
      --badge-text: #412D15;
      --nav-bg: rgba(248, 246, 240, 0.9);
      --card-shadow: 0 4px 20px rgba(31, 21, 12, 0.06);
      --brand-grad: linear-gradient(135deg, #1F150C, #412D15);
      --brand-icon-color: #E1DCC9;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
      background-color: var(--bg);
      color: var(--text);
      line-height: 1.6;
      overflow-x: hidden;
      -webkit-font-smoothing: antialiased;
      transition: background-color 0.25s ease, color 0.25s ease;
    }

    a {
      color: inherit;
      text-decoration: none;
    }

    /* ── Header Navbar ── */
    header {
      position: sticky;
      top: 0;
      z-index: 100;
      backdrop-filter: blur(14px);
      background: var(--nav-bg);
      border-bottom: 1px solid var(--surface-border);
      transition: background-color 0.25s ease, border-color 0.25s ease;
    }

    .nav-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0.9rem 1.5rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      font-weight: 800;
      font-size: 1.15rem;
      letter-spacing: -0.02em;
      min-width: 0;
      flex-shrink: 0;
    }

    .brand-icon {
      width: 40px;
      height: 40px;
      border-radius: 12px;
      background: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 3px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
      border: 1px solid var(--surface-border);
      flex-shrink: 0;
    }

    .brand-icon img {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }

    .brand-text {
      display: flex;
      flex-direction: column;
      line-height: 1.15;
    }

    .brand-title {
      font-weight: 800;
      font-size: 1.15rem;
      letter-spacing: -0.02em;
      white-space: nowrap;
    }

    .brand-sub {
      font-size: 0.75rem;
      color: var(--text-muted);
      font-weight: 500;
      white-space: nowrap;
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 2rem;
      font-size: 0.925rem;
      font-weight: 500;
      color: var(--text-muted);
    }

    .nav-links a:hover {
      color: var(--text);
    }

    .nav-actions {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    /* ── Theme Switcher ── */
    .btn-theme {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 38px;
      height: 38px;
      border-radius: 8px;
      background: var(--surface);
      border: 1px solid var(--surface-border);
      color: var(--text);
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .btn-theme:hover {
      background: var(--surface-hover);
      border-color: var(--border-strong);
    }

    [data-theme="dark"] .sun-icon { display: block; }
    [data-theme="dark"] .moon-icon { display: none; }
    [data-theme="light"] .sun-icon { display: none; }
    [data-theme="light"] .moon-icon { display: block; }

    /* ── Buttons ── */
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      padding: 0.65rem 1.25rem;
      font-size: 0.9rem;
      font-weight: 600;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.2s ease;
      border: 1px solid transparent;
      white-space: nowrap;
    }

    .btn-primary {
      background: var(--primary);
      color: var(--primary-text);
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
    }

    .btn-primary:hover {
      background: var(--primary-hover);
      color: var(--primary-text);
      transform: translateY(-1px);
    }

    .btn-outline {
      background: transparent;
      border-color: var(--surface-border);
      color: var(--text);
    }

    .btn-outline:hover {
      background: var(--surface);
      border-color: var(--border-strong);
    }

    /* ── Hero Section ── */
    .hero {
      max-width: 1200px;
      margin: 0 auto;
      padding: 5rem 1.5rem 3.5rem;
      text-align: center;
    }

    .badge-pill {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.4rem 1rem;
      background: var(--badge-bg);
      border: 1px solid var(--badge-border);
      border-radius: 999px;
      font-size: 0.825rem;
      font-weight: 600;
      color: var(--badge-text);
      margin-bottom: 1.75rem;
    }

    .hero h1 {
      font-size: clamp(2.25rem, 5vw, 3.75rem);
      font-weight: 800;
      letter-spacing: -0.03em;
      line-height: 1.15;
      margin-bottom: 1.25rem;
      max-width: 900px;
      margin-left: auto;
      margin-right: auto;
    }

    .hero p {
      font-size: clamp(1rem, 2vw, 1.2rem);
      color: var(--text-muted);
      max-width: 720px;
      margin: 0 auto 2.5rem;
      line-height: 1.6;
    }

    .hero-buttons {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: center;
      gap: 1rem;
      margin-bottom: 4rem;
    }

    /* ── Metrics Bar ── */
    .metrics {
      max-width: 1100px;
      margin: 0 auto 5rem;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 1.25rem;
      padding: 0 1.5rem;
    }

    .metric-card {
      background: var(--surface);
      border: 1px solid var(--surface-border);
      border-radius: 14px;
      padding: 1.75rem;
      text-align: center;
      box-shadow: var(--card-shadow);
      transition: all 0.2s ease;
    }

    .metric-num {
      font-size: 2.25rem;
      font-weight: 800;
      color: var(--primary);
      line-height: 1;
      margin-bottom: 0.4rem;
    }

    .metric-label {
      font-size: 0.875rem;
      color: var(--text-muted);
      font-weight: 500;
    }

    /* ── Features Section ── */
    .section {
      max-width: 1200px;
      margin: 0 auto;
      padding: 4rem 1.5rem;
    }

    .section-head {
      text-align: center;
      margin-bottom: 3.5rem;
    }

    .section-head h2 {
      font-size: 2rem;
      font-weight: 800;
      letter-spacing: -0.02em;
      margin-bottom: 0.75rem;
    }

    .section-head p {
      color: var(--text-muted);
      max-width: 600px;
      margin: 0 auto;
      font-size: 1rem;
    }

    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1.5rem;
    }

    .feature-card {
      background: var(--surface);
      border: 1px solid var(--surface-border);
      border-radius: 14px;
      padding: 2rem;
      box-shadow: var(--card-shadow);
      transition: transform 0.2s ease, border-color 0.2s ease;
    }

    .feature-card:hover {
      transform: translateY(-3px);
      border-color: var(--border-strong);
    }

    .feature-icon-box {
      width: 44px;
      height: 44px;
      border-radius: 8px;
      background: var(--badge-bg);
      border: 1px solid var(--surface-border);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--primary);
      margin-bottom: 1.25rem;
    }

    .feature-card h3 {
      font-size: 1.15rem;
      font-weight: 700;
      margin-bottom: 0.65rem;
    }

    .feature-card p {
      color: var(--text-muted);
      font-size: 0.925rem;
      line-height: 1.55;
    }

    /* ── Workflow Steps ── */
    .steps-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 1.5rem;
    }

    .step-card {
      background: var(--surface);
      border: 1px solid var(--surface-border);
      border-radius: 14px;
      padding: 2rem;
      box-shadow: var(--card-shadow);
      position: relative;
    }

    .step-number {
      font-size: 0.825rem;
      font-weight: 800;
      color: var(--primary);
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: 0.5rem;
    }

    .step-card h4 {
      font-size: 1.1rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }

    .step-card p {
      color: var(--text-muted);
      font-size: 0.9rem;
      line-height: 1.5;
    }

    /* ── CTA Banner ── */
    .cta-box {
      max-width: 1100px;
      margin: 4rem auto;
      padding: 3.5rem 2rem;
      background: linear-gradient(135deg, var(--surface), var(--bg-subtle));
      border: 1px solid var(--surface-border);
      border-radius: 20px;
      text-align: center;
      box-shadow: var(--card-shadow);
    }

    .cta-box h2 {
      font-size: clamp(1.75rem, 3.5vw, 2.5rem);
      font-weight: 800;
      margin-bottom: 1rem;
      letter-spacing: -0.02em;
    }

    .cta-box p {
      color: var(--text-muted);
      max-width: 580px;
      margin: 0 auto 2rem;
    }

    /* ── Footer ── */
    footer {
      border-top: 1px solid var(--surface-border);
      background: var(--surface);
      padding: 3.5rem 1.5rem 2rem;
      color: var(--text-muted);
      font-size: 0.875rem;
    }

    .footer-container {
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      gap: 2rem;
      padding-bottom: 2.5rem;
    }

    .footer-brand {
      max-width: 320px;
    }

    .footer-brand p {
      margin-top: 0.75rem;
      line-height: 1.5;
    }

    .footer-bottom {
      max-width: 1200px;
      margin: 0 auto;
      padding-top: 1.5rem;
      border-top: 1px solid var(--surface-border);
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
    }

    @media (max-width: 768px) {
      .nav-links {
        display: none;
      }
      .nav-container {
        padding: 0.65rem 0.85rem;
        gap: 0.5rem;
      }
      .brand {
        gap: 0.5rem;
      }
      .brand-icon {
        width: 36px;
        height: 36px;
      }
      .brand-title {
        font-size: 1.05rem;
        white-space: nowrap;
      }
      .brand-sub {
        font-size: 0.68rem;
        white-space: nowrap;
      }
      .nav-actions {
        gap: 0.35rem;
      }
      .btn-nav-calendar {
        display: none !important;
      }
      .btn-nav-login {
        padding: 0.45rem 0.75rem;
        font-size: 0.825rem;
        white-space: nowrap;
      }
      .btn-nav-cta {
        padding: 0.45rem 0.75rem;
        font-size: 0.825rem;
        white-space: nowrap;
      }
      .hero {
        padding: 2.75rem 1rem 2rem;
      }
      .hero h1 {
        font-size: 2.15rem;
        line-height: 1.2;
      }
      .hero p {
        font-size: 0.95rem;
        margin-bottom: 2rem;
      }
      .hero-buttons {
        flex-direction: column;
        width: 100%;
        max-width: 320px;
        margin-left: auto;
        margin-right: auto;
        gap: 0.75rem;
        margin-bottom: 3rem;
      }
      .hero-buttons .btn {
        width: 100%;
        justify-content: center;
      }
      .metrics {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
        padding: 0 1rem;
        margin-bottom: 3rem;
      }
      .metric-card {
        padding: 1.25rem 0.75rem;
      }
      .metric-num {
        font-size: 1.75rem;
      }
      .metric-label {
        font-size: 0.75rem;
      }
      .cta-box {
        padding: 2.5rem 1.25rem;
        margin: 2.5rem 1rem;
      }
    }
  </style>
</head>
<body>

  <!-- Navigation -->
  <header>
    <div class="nav-container">
      <a href="{{ url('/') }}" class="brand">
        <div class="brand-icon">
          <img src="{{ asset('assets/img/logo-smk.png') }}" alt="Logo SMK N 1 Bantul">
        </div>
        <div class="brand-text">
          <span class="brand-title">RAPI-Sekolah</span>
          <span class="brand-sub">SMK Negeri 1 Bantul</span>
        </div>
      </a>

      <nav class="nav-links">
        <a href="#fitur">Layanan</a>
        <a href="#alur">Alur Kerja</a>
        <a href="#responsif">Multi-Perangkat</a>
        <a href="{{ route('calendar') }}">Kalender Ruang</a>
      </nav>

      <div class="nav-actions">
        <!-- Theme Toggle Button -->
        <button id="theme-toggle" class="btn-theme" type="button" aria-label="Ganti Mode Tema" title="Ganti Mode Terang / Gelap">
          <!-- Sun Icon (shown in dark mode) -->
          <svg class="sun-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="5"/>
            <line x1="12" y1="1" x2="12" y2="3"/>
            <line x1="12" y1="21" x2="12" y2="23"/>
            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
            <line x1="1" y1="12" x2="3" y2="12"/>
            <line x1="21" y1="12" x2="23" y2="12"/>
            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
          </svg>
          <!-- Moon Icon (shown in light mode) -->
          <svg class="moon-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
          </svg>
        </button>

        @auth
          <a href="{{ route('calendar') }}" class="btn btn-primary btn-nav-cta">
            Dashboard
          </a>
        @else
          <a href="{{ route('login') }}" class="btn btn-outline btn-nav-login">
            Masuk
          </a>
          <a href="{{ route('calendar') }}" class="btn btn-primary btn-nav-calendar">
            Cek Ruangan
          </a>
        @endauth
      </div>
    </div>
  </header>

  <!-- Hero Section -->
  <section class="hero">
    <div class="badge-pill">
      <img src="{{ asset('assets/img/logo-smk.png') }}" alt="Logo SMK" style="width:20px;height:20px;object-fit:contain;flex-shrink:0;">
      <span>Sistem Sarpras Terpadu &middot; SMK Negeri 1 Bantul</span>
    </div>

    <h1>Digitalisasi Pengelolaan Ruang &amp; Fasilitas Sekolah</h1>

    <p>
      Platform terintegrasi untuk peminjaman laboratorium, ruang rapat, koordinasi agenda sekolah, dan pelaporan perbaikan fasilitas secara transparan dan terdata akurat.
    </p>

    <div class="hero-buttons">
      <a href="{{ route('calendar') }}" class="btn btn-primary" style="padding:0.85rem 1.75rem;font-size:1rem;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
          <line x1="16" y1="2" x2="16" y2="6"/>
          <line x1="8" y1="2" x2="8" y2="6"/>
          <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        Buka Kalender Ruang
      </a>

      <a href="{{ route('login') }}" class="btn btn-outline" style="padding:0.85rem 1.75rem;font-size:1rem;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
          <polyline points="10 17 15 12 10 7"/>
          <line x1="15" y1="12" x2="3" y2="12"/>
        </svg>
        Masuk Sistem
      </a>
    </div>
  </section>

  <!-- Live Metrics Bar -->
  <div class="metrics">
    <div class="metric-card">
      <div class="metric-num">23+</div>
      <div class="metric-label">Laboratorium &amp; Ruang Pertemuan</div>
    </div>
    <div class="metric-card">
      <div class="metric-num">100%</div>
      <div class="metric-label">Validasi Bentrok Otomatis</div>
    </div>
    <div class="metric-card">
      <div class="metric-num">5 Role</div>
      <div class="metric-label">Admin, Waka, PIC, Guru, Organisasi</div>
    </div>
    <div class="metric-card">
      <div class="metric-num">Real-Time</div>
      <div class="metric-label">Sinkronisasi Kalender Agenda</div>
    </div>
  </div>

  <!-- Features Grid -->
  <section class="section" id="fitur">
    <div class="section-head">
      <h2>Fitur Terpadu Manajemen Sarpras</h2>
      <p>Dirancang sesuai standar operasional pengelolaan sarana dan prasarana pendidikan formal.</p>
    </div>

    <div class="features-grid">
      <!-- 1 -->
      <div class="feature-card">
        <div class="feature-icon-box">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
        </div>
        <h3>Kalender &amp; Booking Multi-Hari</h3>
        <p>Peminjaman ruang fleksibel per sesi hingga beberapa hari berturut-turut dengan pencegahan jadwal ganda secara otomatis.</p>
      </div>

      <!-- 2 -->
      <div class="feature-card">
        <div class="feature-icon-box">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
          </svg>
        </div>
        <h3>Pengaduan &amp; Perbaikan Fasilitas</h3>
        <p>Laporan kerusakan perangkat dan inventaris dilengkapi foto bukti, status penanganan, dan disposisi langsung ke PIC ruangan.</p>
      </div>

      <!-- 3 -->
      <div class="feature-card">
        <div class="feature-icon-box">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
        </div>
        <h3>Struktur Penanggung Jawab (PIC)</h3>
        <p>Pemetaan penanggung jawab ruangan laboratorium secara spesifik untuk mempermudah koordinasi operasional.</p>
      </div>

      <!-- 4 -->
      <div class="feature-card">
        <div class="feature-icon-box">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
            <line x1="8" y1="21" x2="16" y2="21"/>
            <line x1="12" y1="17" x2="12" y2="21"/>
          </svg>
        </div>
        <h3>Desain 100% Responsif</h3>
        <p>Akses lancar dari seluruh perangkat &mdash; laptop, tablet, hingga smartphone (Android &amp; iPhone) langsung lewat browser tanpa perlu instal aplikasi.</p>
      </div>

      <!-- 5 -->
      <div class="feature-card">
        <div class="feature-icon-box">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
            <polyline points="10 9 9 9 8 9"/>
          </svg>
        </div>
        <h3>Rekapitulasi Laporan Sarpras</h3>
        <p>Penyusunan data riwayat utilisasi ruangan dan catatan penanganan sarpras untuk pelaporan berkala pimpinan sekolah.</p>
      </div>

      <!-- 6 -->
      <div class="feature-card">
        <div class="feature-icon-box">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
        </div>
        <h3>Persetujuan Bertingkat Waka</h3>
        <p>Alur verifikasi terstruktur oleh Waka Bidang Sarana &amp; Prasarana sebelum ruangan dinyatakan resmi terpesan.</p>
      </div>
    </div>
  </section>

  <!-- Workflow Steps -->
  <section class="section" id="alur" style="padding-top:1rem;">
    <div class="section-head">
      <h2>Alur Peminjaman Ruangan</h2>
      <p>Prosedur standar pengajuan dan penggunaan fasilitas sekolah.</p>
    </div>

    <div class="steps-grid">
      <div class="step-card">
        <div class="step-number">Langkah 01</div>
        <h4>Cek Ketersediaan</h4>
        <p>Lihat kalender ruang untuk memastikan laboratorium atau fasilitas yang dituju berstatus bebas pada sesi yang diinginkan.</p>
      </div>

      <div class="step-card">
        <div class="step-number">Langkah 02</div>
        <h4>Ajukan Peminjaman</h4>
        <p>Masuk menggunakan akun Guru atau Organisasi, lalu isi detail kegiatan, rentang waktu, dan keperluan acara.</p>
      </div>

      <div class="step-card">
        <div class="step-number">Langkah 03</div>
        <h4>Verifikasi &amp; Penggunaan</h4>
        <p>Waka Sarpras melakukan persetujuan. Jadwal langsung terkunci di sistem dan siap digunakan tanpa risiko bentrok.</p>
      </div>
    </div>
  </section>

  <!-- Responsive Web Section -->
  <section class="section" id="responsif" style="padding-top:1rem;">
    <div class="cta-box">
      <div style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.35rem 0.9rem;background:var(--badge-bg);border:1px solid var(--badge-border);border-radius:999px;font-size:0.825rem;font-weight:600;color:var(--badge-text);margin-bottom:1.25rem;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
          <line x1="8" y1="21" x2="16" y2="21"/>
          <line x1="12" y1="17" x2="12" y2="21"/>
        </svg>
        Akses Cepat &amp; Fleksibel
      </div>
      <h2>Optimal di Seluruh Perangkat</h2>
      <p>Buka langsung lewat browser HP Android, iPhone, tablet, maupun laptop tanpa perlu instal aplikasi tambahan. Ringan, cepat, dan selalu terbarui.</p>
      <div style="display:flex;justify-content:center;gap:1rem;flex-wrap:wrap">
        <a href="{{ route('calendar') }}" class="btn btn-primary" style="padding:0.85rem 1.75rem;font-size:1rem;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
          Buka Kalender Ruang
        </a>
        <a href="{{ route('login') }}" class="btn btn-outline" style="padding:0.85rem 1.75rem;font-size:1rem;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
            <polyline points="10 17 15 12 10 7"/>
            <line x1="15" y1="12" x2="3" y2="12"/>
          </svg>
          Masuk Akun
        </a>
      </div>
      <div style="margin-top:1.25rem;font-size:0.825rem;color:var(--text-muted);">
        Kompatibel dengan Google Chrome, Safari, Microsoft Edge, &amp; Mozilla Firefox
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <div class="footer-container">
      <div class="footer-brand">
        <div class="brand" style="margin-bottom:0.5rem">
          <div class="brand-icon">
            <img src="{{ asset('assets/img/logo-smk.png') }}" alt="Logo SMK N 1 Bantul">
          </div>
          <div class="brand-text">
            <span class="brand-title">RAPI-Sekolah</span>
            <span class="brand-sub">SMK Negeri 1 Bantul</span>
          </div>
        </div>
        <p>Sistem Pengelolaan Sarana dan Prasarana SMK Negeri 1 Bantul. Menunjang transparansi, ketertiban, dan pemanfaatan fasilitas pendidikan secara optimal.</p>
      </div>

      <div>
        <h4 style="color:var(--text);margin-bottom:1rem;font-size:0.95rem">Navigasi Cepat</h4>
        <ul style="list-style:none;display:grid;gap:0.5rem">
          <li><a href="{{ route('calendar') }}">Kalender Ketersediaan</a></li>
          <li><a href="{{ route('login') }}">Masuk Akun</a></li>
          <li><a href="#fitur">Layanan Sarpras</a></li>
          <li><a href="#alur">Alur Peminjaman</a></li>
          <li><a href="#responsif">Akses Multi-Perangkat</a></li>
        </ul>
      </div>

      <div>
        <h4 style="color:var(--text);margin-bottom:1rem;font-size:0.95rem">Hak Akses Sistem</h4>
        <ul style="list-style:none;display:grid;gap:0.5rem">
          <li>Administrator Sistem</li>
          <li>Waka Bidang Sarpras</li>
          <li>PIC / Penanggung Jawab Lab</li>
          <li>Dewan Guru &amp; Tenaga Kependidikan</li>
          <li>Pengurus OSIS / Organisasi</li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <div>&copy; {{ date('Y') }} RAPI-Sekolah. Seluruh hak cipta dilindungi.</div>
      <div style="color:var(--text-muted)">Sistem Sarpras Terpadu</div>
    </div>
  </footer>

  <!-- Theme Toggle Script -->
  <script>
    (function() {
      const toggleBtn = document.getElementById('theme-toggle');
      if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
          const current = document.documentElement.getAttribute('data-theme') || 'dark';
          const next = current === 'dark' ? 'light' : 'dark';
          document.documentElement.setAttribute('data-theme', next);
          localStorage.setItem('theme', next);
        });
      }
    })();
  </script>

</body>
</html>
