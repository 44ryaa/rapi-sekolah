<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#16120f">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="RAPI-Sekolah">
<meta name="format-detection" content="telephone=no">
<title>RAPI-Sekolah · Sarpras Sekolah</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
@stack('head')
</head>
<body>

@php
  $user       = auth()->user();
  $unread     = $user ? $user->unreadNotifications()->latest()->limit(10)->get() : collect();
  $initials   = $user ? strtoupper(substr($user->fullname ?? $user->username, 0, 1)) : '?';
  $route      = request()->route()?->getName() ?? '';
  $isActive   = fn($prefix) => str_starts_with($route, $prefix) ? 'active' : '';

  // Determine current page title
  $pageTitle = match(true) {
      str_starts_with($route, 'calendar')   => 'Kalender Sarpras',
      str_starts_with($route, 'events')     => 'Agenda & Peminjaman Ruang',
      str_starts_with($route, 'complaints') => 'Pengaduan & Perbaikan',
      str_starts_with($route, 'admin.rooms')=> 'Kelola Ruang & PIC',
      str_starts_with($route, 'admin.users')=> 'Manajemen Pengguna',
      str_starts_with($route, 'profile')    => 'Pengaturan Akun',
      default => 'Overview Sistem',
  };
@endphp

{{-- ═══════════════ MOBILE TOPBAR ═══════════════ --}}
<header class="topbar" id="topbar">
  <button class="hamburger" id="hamburger" type="button" aria-label="Buka menu navigasi" aria-expanded="false">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
  </button>
  <a href="{{ route('calendar') }}" class="topbar-brand">
    <div class="brand-mark-squircle topbar-squircle">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polygon points="12 2 2 7 12 12 22 7 12 2"/>
        <polyline points="2 17 12 22 22 17"/>
        <polyline points="2 12 12 17 22 12"/>
      </svg>
    </div>
    <div class="topbar-brand-text">
      <span class="topbar-brand-title">RAPI-Sekolah</span>
      <span class="topbar-page-label">{{ $pageTitle }}</span>
    </div>
  </a>
  <div class="topbar-right">
    @auth
      <button type="button" class="header-icon-btn topbar-icon-btn" id="openMobileNotifBtn" title="Pemberitahuan">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        @if($unread->isNotEmpty())<span class="notif-badge-dot"></span>@endif
      </button>
      <a href="{{ route('profile') }}" class="header-user-avatar topbar-user-avatar" title="Profil">
        {{ $initials }}
      </a>
    @else
      <a class="btn sm" href="{{ route('login') }}">Masuk</a>
    @endauth
  </div>
</header>

{{-- ═══════════════ MAIN DASHBOARD CONTAINER (Image 2 style) ═══════════════ --}}
<div class="app-container">

  {{-- ── Left Sidebar (Dark Espresso with Cream Squircle) ── --}}
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <div class="brand-mark-squircle">
        {{-- Custom stylish geometric stack glyph like image 2 top-left --}}
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <polygon points="12 2 2 7 12 12 22 7 12 2"/>
          <polyline points="2 17 12 22 22 17"/>
          <polyline points="2 12 12 17 22 12"/>
        </svg>
      </div>
      <div class="brand-info">
        <span class="brand-title">RAPI-Sekolah</span>
        <span class="brand-sub">Sistem Sarpras Terpadu</span>
      </div>
      <button class="sidebar-close" id="sidebarClose" type="button" aria-label="Tutup menu">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <nav class="sidebar-nav">
      <span class="nav-section-title">Menu Utama</span>

      {{-- Kalender --}}
      <a href="{{ route('calendar') }}" class="nav-link {{ $isActive('calendar') }}">
        <span class="nav-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </span>
        Kalender Ruang
      </a>

      @auth
        {{-- Agenda (Admin, Guru, Waka) --}}
        @if($user->isAdmin() || $user->isGuru() || $user->isWaka())
        <a href="{{ route('events.index') }}" class="nav-link {{ $isActive('events') }}">
          <span class="nav-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
          </span>
          Agenda Ruang
        </a>
        @endif

        {{-- Pengaduan --}}
        <a href="{{ route('complaints.index') }}" class="nav-link {{ $isActive('complaints') }}">
          <span class="nav-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
          </span>
          Pengaduan
          @if($unread->isNotEmpty())
            <span class="nav-pill-badge">{{ $unread->count() }}</span>
          @endif
        </a>

        {{-- Kelola: Admin & Waka (Ruang) --}}
        @if($user->isAdmin() || $user->isWaka())
          <span class="nav-section-title">Manajemen</span>
          <a href="{{ route('admin.rooms') }}" class="nav-link {{ $isActive('admin.rooms') }}">
            <span class="nav-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 4H3v16h18V9z"/><polyline points="13 4 13 9 21 9"/><circle cx="16" cy="14" r="1" fill="currentColor"/></svg>
            </span>
            Ruang &amp; PIC
          </a>
        @endif

        {{-- Kelola: Admin Only (Pengguna) --}}
        @if($user->isAdmin())
          <a href="{{ route('admin.users') }}" class="nav-link {{ $isActive('admin.users') }}">
            <span class="nav-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </span>
            Data Pengguna
          </a>
        @endif

        <span class="nav-section-title">Akun</span>
        <a href="{{ route('profile') }}" class="nav-link {{ $isActive('profile') }}">
          <span class="nav-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
          </span>
          Profil Saya
        </a>
      @else
        <a href="{{ route('login') }}" class="nav-link">
          <span class="nav-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
          </span>
          Masuk Akun
        </a>
      @endauth
    </nav>

    @auth
    <div class="sidebar-footer">
      <div class="user-chip">
        <div class="user-avatar-sq">{{ $initials }}</div>
        <div class="user-meta">
          <strong>{{ $user->fullname ?? $user->username }}</strong>
          <span>{{ $user->role->role_name ?? 'User' }}</span>
        </div>
      </div>
      <form method="post" action="{{ route('logout') }}" style="margin:0">@csrf
        <button type="submit" class="btn-logout" title="Keluar">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </button>
      </form>
    </div>
    @endauth
  </aside>

  {{-- ── Main Canvas (Rounded Cream Dashboard Card) ── --}}
  <main class="main-canvas">

    {{-- Top Canvas Header (Search, Title, Notif, User Pill) --}}
    <header class="canvas-header">
      <div class="canvas-header-left">
        <h1 class="canvas-header-title">{{ $pageTitle }}</h1>
      </div>

      <div class="canvas-header-right">
        {{-- Realtime live search input --}}
        <div class="header-search">
          <span class="search-icon">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          </span>
          <input type="text" id="liveSearchInput" placeholder="Cari ruang, agenda, pengaduan..." autocomplete="off">
        </div>

        @auth
          {{-- Notification Bell --}}
          <button type="button" class="header-icon-btn" id="openNotifBtn" title="Lihat pemberitahuan">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            @if($unread->isNotEmpty())
              <span class="notif-badge-dot"></span>
            @endif
          </button>

          {{-- User Status Pill --}}
          <a href="{{ route('profile') }}" class="header-user-pill" title="Profil">
            <span class="header-user-avatar">{{ $initials }}</span>
            <span class="header-user-name">{{ explode(' ', $user->fullname ?? $user->username)[0] }}</span>
            <span class="header-role-tag">{{ $user->role->role_name ?? 'User' }}</span>
          </a>
        @else
          <a class="btn sm" href="{{ route('login') }}">Masuk</a>
        @endauth
      </div>
    </header>

    {{-- Canvas Content Body --}}
    <div class="canvas-content">
      @if(session('success'))
        <div class="flash ok" role="status">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          {{ session('success') }}
        </div>
      @endif

      @if($errors->any())
        @foreach($errors->all() as $err)
          <div class="flash error" role="alert">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ $err }}
          </div>
        @endforeach
      @endif

      @yield('content')
    </div>
  </main>
</div>

{{-- ═══════════════ MOBILE BOTTOM NAVIGATION ═══════════════ --}}
<nav class="tabs" aria-label="Menu mobile">
  <a href="{{ route('calendar') }}" class="{{ $isActive('calendar') }}">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    <span>Kalender</span>
  </a>
  @auth
    @if($user->isAdmin() || $user->isGuru() || $user->isWaka())
    <a href="{{ route('events.index') }}" class="{{ $isActive('events') }}">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      <span>Agenda</span>
    </a>
    @endif

    <a href="{{ route('complaints.index') }}" class="{{ $isActive('complaints') }}">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
      <span>Pengaduan</span>
      @if($unread->isNotEmpty())
        <span style="position:absolute;top:4px;right:25%;background:#dc2626;color:#fff;font-size:.6rem;padding:1px 5px;border-radius:10px">{{ $unread->count() }}</span>
      @endif
    </a>

    @if($user->isAdmin() || $user->isWaka())
    <a href="{{ route('admin.rooms') }}" class="{{ $isActive('admin') }}">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 4H3v16h18V9z"/><polyline points="13 4 13 9 21 9"/></svg>
      <span>Ruang</span>
    </a>
    @endif

    <a href="{{ route('profile') }}" class="{{ $isActive('profile') }}">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/></svg>
      <span>Profil</span>
    </a>
  @else
    <a href="{{ route('login') }}" class="{{ $isActive('login') }}">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
      <span>Masuk</span>
    </a>
  @endauth
</nav>

{{-- ═══════════════ NOTIFICATION MODAL (Opened on click, NO auto popup delay!) ═══════════════ --}}
@auth
<dialog id="notifDialog" class="modal" aria-labelledby="ntTitle">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
    <h2 id="ntTitle" style="display:flex;align-items:center;gap:.5rem;margin:0">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
      Pemberitahuan
    </h2>
    <button type="button" class="btn ghost sm" data-close style="border:0;padding:.3rem .5rem">✕</button>
  </div>
  @if($unread->isNotEmpty())
    <ul class="notif-list" style="list-style:none;padding:0;margin:0 0 1.25rem;display:grid;gap:.65rem">
      @foreach($unread as $n)
        <li style="background:var(--c-cream-soft);padding:.75rem .95rem;border-radius:10px;border-left:3px solid var(--accent);font-size:.88rem">
          {{ $n->message }}
          <time style="display:block;color:var(--muted);font-size:.72rem;margin-top:4px">{{ $n->created_at->format('d M Y · H:i') }}</time>
        </li>
      @endforeach
    </ul>
    <form method="post" action="{{ route('notifications.read') }}">@csrf
      <button class="btn block success">Tandai sudah dibaca &amp; tutup</button>
    </form>
  @else
    <p class="muted center" style="padding:1.5rem 0">Tidak ada pemberitahuan baru.</p>
    <button class="btn block ghost" type="button" data-close>Tutup</button>
  @endif
</dialog>
@endauth

{{-- Floating Toast Container --}}
<div id="toastContainer" class="toast-container"></div>

{{-- ═══════════════ CORE SCRIPTS ═══════════════ --}}
<script>
window.RAPI = {
  api: '{{ route('calendar.api') }}',
  slots: '{{ route('events.slots') }}'
};
</script>
<script>
// Sidebar Toggle (Mobile)
const sidebar      = document.getElementById('sidebar');
const hamburger    = document.getElementById('hamburger');
const sidebarClose = document.getElementById('sidebarClose');
const openNotifBtn = document.getElementById('openNotifBtn');
const openMobNotif = document.getElementById('openMobileNotifBtn');
const notifDialog  = document.getElementById('notifDialog');

const overlay = document.createElement('div');
overlay.className = 'sidebar-overlay';
document.body.appendChild(overlay);

function openSidebar()  { sidebar?.classList.add('open'); overlay.classList.add('show'); document.body.style.overflow='hidden'; }
function closeSidebar() { sidebar?.classList.remove('open'); overlay.classList.remove('show'); document.body.style.overflow=''; }

hamburger?.addEventListener('click', openSidebar);
sidebarClose?.addEventListener('click', closeSidebar);
overlay.addEventListener('click', closeSidebar);

// Open Notification Dialog on user click ONLY (NO 400ms auto delay)
openNotifBtn?.addEventListener('click', () => notifDialog?.showModal());
openMobNotif?.addEventListener('click', () => notifDialog?.showModal());

document.querySelectorAll('[data-close]').forEach(b => b.addEventListener('click', () => b.closest('dialog')?.close()));
document.querySelectorAll('[data-confirm]').forEach(form => form.addEventListener('submit', e => { if (!confirm(form.dataset.confirm)) e.preventDefault(); }));

// Toast Notification Helper
function showToast(message, type = 'ok') {
  const container = document.getElementById('toastContainer');
  if (!container) return;
  const item = document.createElement('div');
  item.className = 'toast-item ' + type;
  const icon = type === 'ok' ? '✓' : '!';
  item.innerHTML = `<span style="font-weight:800">${icon}</span> <span>${message}</span>`;
  container.appendChild(item);
  setTimeout(() => {
    item.style.transition = 'opacity .3s, transform .3s';
    item.style.opacity = '0';
    item.style.transform = 'translateX(50px)';
    setTimeout(() => item.remove(), 300);
  }, 3500);
}

// Live Search Filter across item cards
const searchInput = document.getElementById('liveSearchInput');
if (searchInput) {
  searchInput.addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    const items = document.querySelectorAll('.card.item, .agenda li');
    items.forEach(el => {
      const text = el.textContent.toLowerCase();
      if (!q || text.includes(q)) {
        el.style.display = '';
      } else {
        el.style.display = 'none';
      }
    });
  });
}
</script>
<script src="{{ asset('assets/js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
