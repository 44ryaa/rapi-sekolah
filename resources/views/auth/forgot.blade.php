@extends('layouts.app')
@section('content')
<section class="card narrow" style="max-width:440px;margin:2.5rem auto;padding:2.25rem 2rem">
  <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.25rem">
    <div class="item-icon-squircle" style="width:48px;height:48px;border-radius:14px">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
    </div>
    <div>
      <h1 style="margin:0;font-size:1.35rem">Lupa Password</h1>
      <p class="muted" style="margin:0;font-size:.82rem">Verifikasi akun untuk membuat password baru</p>
    </div>
  </div>

  <p class="muted" style="margin-bottom:1.25rem;font-size:.86rem;line-height:1.6">
    Masukkan <b>Username</b> (atau Email) dan <b>Nomor HP</b> yang terdaftar pada akun Anda. Setelah data terverifikasi, Anda dapat langsung mengatur ulang password baru.
  </p>

  <form method="post" action="{{ route('forgot') }}" class="form">
    @csrf
    <label>Username atau Email
      <input name="username" required autocomplete="username" placeholder="Contoh: guru1 atau admin" value="{{ old('username') }}" autofocus>
    </label>

    <label>Nomor HP Terdaftar
      <input name="phone" inputmode="tel" placeholder="Contoh: 08111000004" required autocomplete="tel" value="{{ old('phone') }}">
    </label>

    <button type="submit" class="btn block" style="margin-top:.75rem;min-height:46px;font-size:.95rem">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
      Verifikasi &amp; Lanjut
    </button>
  </form>

  <p class="center" style="margin-top:1.5rem;font-size:.85rem">
    <a href="{{ route('login') }}" style="display:inline-flex;align-items:center;gap:.35rem">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
      Kembali ke halaman masuk
    </a>
  </p>
</section>
@endsection
