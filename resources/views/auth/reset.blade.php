@extends('layouts.app')
@section('content')
<section class="card narrow" style="max-width:440px;margin:2.5rem auto;padding:2.25rem 2rem">
  <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.25rem">
    <div class="item-icon-squircle" style="width:48px;height:48px;border-radius:14px">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
    </div>
    <div>
      <h1 style="margin:0;font-size:1.35rem">Buat Password Baru</h1>
      <p class="muted" style="margin:0;font-size:.82rem">Atur kata sandi baru untuk akun Anda</p>
    </div>
  </div>

  <p class="muted" style="margin-bottom:1.25rem;font-size:.85rem;line-height:1.6">
    Tautan verifikasi ini berlaku selama 30 menit. Masukkan password baru dengan minimal 6 karakter.
  </p>

  <form method="post" action="{{ route('reset') }}" class="form">
    @csrf
    <input type="hidden" name="token" value="{{ $token ?? old('token', request('token', '')) }}">

    <label>Password Baru
      <input type="password" name="password" minlength="6" required autocomplete="new-password" placeholder="Minimal 6 karakter" autofocus>
    </label>

    <label>Ulangi Password Baru
      <input type="password" name="password2" minlength="6" required autocomplete="new-password" placeholder="Ketik ulang password baru">
    </label>

    <button type="submit" class="btn block" style="margin-top:.75rem;min-height:46px;font-size:.95rem">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
      Simpan Password Baru
    </button>
  </form>

  <p class="center" style="margin-top:1.5rem;font-size:.85rem">
    <a href="{{ route('login') }}" style="display:inline-flex;align-items:center;gap:.35rem">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
      Batal &amp; Kembali ke Halaman Masuk
    </a>
  </p>
</section>
@endsection
