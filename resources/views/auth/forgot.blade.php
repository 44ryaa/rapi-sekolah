@extends('layouts.app')
@section('content')
<section class="card narrow" style="max-width:480px;margin:2rem auto;padding:2.25rem 2rem">
  <div style="display:flex;align-items:center;gap:.85rem;margin-bottom:1.25rem">
    <div class="item-icon-squircle" style="width:48px;height:48px;border-radius:14px;background:var(--c-cream-soft);display:flex;align-items:center;justify-content:center;color:var(--accent)">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
      </svg>
    </div>
    <div>
      <h1 style="margin:0;font-size:1.35rem">Lupa Kata Sandi</h1>
      <p class="muted" style="margin:0;font-size:.82rem">Verifikasi akun via Email terdaftar di database</p>
    </div>
  </div>

  <p class="muted" style="margin-bottom:1.25rem;font-size:.86rem;line-height:1.6">
    Masukkan <b>Username</b> atau <b>Email</b> akun Anda. Sistem akan memverifikasi dan mengirimkan kode OTP secara aman ke <b>email yang terdaftar di database</b>.
  </p>

  <form method="post" action="{{ route('forgot') }}" class="form" id="forgotForm">
    @csrf

    <label>Username atau Email Akun
      <input name="username" required autocomplete="username" placeholder="Contoh: guru1, waka, atau admin" value="{{ old('username') }}" autofocus>
    </label>

    <div style="margin-top:.5rem">
      <span style="display:block;font-size:.82rem;font-weight:600;color:var(--ink-secondary);margin-bottom:.5rem">
        Metode Pengiriman OTP:
      </span>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
        {{-- Option Email Terdaftar --}}
        <label class="channel-card" style="display:flex;flex-direction:column;align-items:flex-start;padding:.9rem;border:2px solid var(--border);border-radius:12px;cursor:pointer;transition:all .2s;background:var(--c-cream-soft)">
          <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.35rem">
            <input type="radio" name="channel" value="email" {{ old('channel', 'email') === 'email' ? 'checked' : '' }}>
            <b style="font-size:.88rem">Email Database</b>
          </div>
          <span style="font-size:.74rem;color:var(--muted)">Sesuai email terdaftar</span>
        </label>

        {{-- Option WhatsApp Terdaftar --}}
        <label class="channel-card" style="display:flex;flex-direction:column;align-items:flex-start;padding:.9rem;border:2px solid var(--border);border-radius:12px;cursor:pointer;transition:all .2s;background:var(--c-cream-soft)">
          <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.35rem">
            <input type="radio" name="channel" value="whatsapp" {{ old('channel') === 'whatsapp' ? 'checked' : '' }}>
            <b style="font-size:.88rem">WhatsApp</b>
          </div>
          <span style="font-size:.74rem;color:var(--muted)">Sesuai No. HP terdaftar</span>
        </label>
      </div>

      <div style="margin-top:.75rem;padding:.75rem .9rem;background:var(--c-cream-soft);border-radius:10px;border-left:3.5px solid var(--accent);font-size:.8rem;line-height:1.5;color:var(--ink-secondary)">
        <b>Keamanan Akun:</b> Kode verifikasi OTP akan dikirimkan secara eksklusif ke alamat email resmi atau nomor HP akun Anda yang tersimpan di database sekolah.
      </div>
    </div>

    <button type="submit" class="btn block" style="margin-top:1.25rem;min-height:46px;font-size:.95rem">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
      Kirim Kode OTP Verifikasi
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
