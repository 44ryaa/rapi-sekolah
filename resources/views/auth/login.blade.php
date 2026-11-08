@extends('layouts.app')
@section('content')
<section class="card narrow" style="padding:2.25rem 2rem;margin-top:1rem">
  <div style="text-align:center;margin-bottom:1.5rem">
    <div style="margin:0 auto 1.25rem;width:68px;height:68px;padding:4px;background:#fff;border-radius:18px;box-shadow:0 6px 20px rgba(0,0,0,0.18);display:flex;align-items:center;justify-content:center;">
      <img src="{{ asset('assets/img/logo-smk.png') }}" alt="Logo SMK Negeri 1 Bantul" style="width:100%;height:100%;object-fit:contain;">
    </div>
    <h1 style="font-size:1.6rem;margin-bottom:.35rem">Masuk ke RAPI-Sekolah</h1>
    <p class="muted" style="font-size:.88rem">SMK Negeri 1 Bantul &middot; Sistem Pengelolaan Sarpras Terpadu</p>
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
    <p style="margin:0"><a href="{{ route('forgot') }}" style="font-size:.85rem">Lupa password akun? Reset via OTP (Gmail &amp; WhatsApp) &rarr;</a></p>
    <p class="muted" style="font-size:.82rem;margin:.5rem 0 0">
      Siswa atau tamu tidak perlu login. <a href="{{ route('calendar') }}">Lihat Jadwal Kalender &rarr;</a>
    </p>
  </div>
</section>
@endsection
