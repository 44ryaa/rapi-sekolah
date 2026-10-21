@extends('layouts.app')
@section('content')
<section class="card narrow" style="padding:2.25rem 2rem;margin-top:1rem">
  <div style="text-align:center;margin-bottom:1.5rem">
    <div class="brand-mark-squircle" style="margin:0 auto 1rem;width:54px;height:54px;border-radius:16px">
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polygon points="12 2 2 7 12 12 22 7 12 2"/>
        <polyline points="2 17 12 22 22 17"/>
        <polyline points="2 12 12 17 22 12"/>
      </svg>
    </div>
    <h1 style="font-size:1.6rem;margin-bottom:.35rem">Masuk ke RAPI-Sekolah</h1>
    <p class="muted" style="font-size:.88rem">Sistem Pengelolaan Sarpras, Ruang &amp; Pengaduan Terpadu</p>
  </div>

  <form method="post" action="{{ route('login') }}" class="form" autocomplete="on">
    @csrf
    <label>Username / ID Pengguna
      <input name="username" required autocomplete="username" autofocus value="{{ old('username') }}">
    </label>
    <label>Kata Sandi
      <input type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
    </label>
    <button class="btn block" style="min-height:46px;font-size:1rem;margin-top:.4rem">Masuk ke Sistem</button>
  </form>

  <div style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--border);text-align:center">
    <p style="margin:0"><a href="{{ route('forgot') }}" style="font-size:.85rem">Lupa password akun?</a></p>
    <p class="muted" style="font-size:.82rem;margin:.5rem 0 0">
      Siswa atau tamu tidak perlu login. <a href="{{ route('calendar') }}">Lihat Jadwal Kalender →</a>
    </p>
  </div>
</section>
@endsection
