@extends('layouts.app')
@section('content')
<section class="card narrow" style="max-width:480px;margin:2rem auto;padding:2.25rem 2rem">
  <div style="display:flex;align-items:center;gap:.85rem;margin-bottom:1.25rem">
    <div class="item-icon-squircle" style="width:48px;height:48px;border-radius:14px;background:#10b981;color:#fff">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    </div>
    <div>
      <h1 style="margin:0;font-size:1.35rem">Masukkan Kode OTP</h1>
      <p class="muted" style="margin:0;font-size:.82rem">Verifikasi 6 digit kode keamanan</p>
    </div>
  </div>

  {{-- Notification Channel info --}}
  <div style="background:var(--c-cream-soft);border:1px solid var(--border);border-radius:12px;padding:1rem;margin-bottom:1.25rem">
    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.35rem">
      @if($channel === 'whatsapp')
        <span style="display:inline-flex;padding:.2rem .55rem;background:#25d366;color:#fff;border-radius:6px;font-size:.75rem;font-weight:700">WhatsApp</span>
        <span style="font-size:.85rem;font-weight:600">{{ $phone ?? '-' }}</span>
      @else
        <span style="display:inline-flex;padding:.2rem .55rem;background:#3b82f6;color:#fff;border-radius:6px;font-size:.75rem;font-weight:700">Gmail / Email</span>
        <span style="font-size:.85rem;font-weight:600">{{ $targetEmail ?? $email }}</span>
      @endif
    </div>
    <p style="margin:0;font-size:.8rem;color:var(--muted);line-height:1.5">
      Kode verifikasi (OTP) 6 digit telah dibuat dan berlaku selama <b>15 menit</b>.
    </p>

    {{-- Tombol Buka WhatsApp jika channel WhatsApp --}}
    @if($channel === 'whatsapp')
      @if(session('reset_wa_auto_sent'))
        <div style="margin-top:.75rem;padding:.6rem .85rem;background:#dcfce7;border:1px solid #86efac;border-radius:8px;font-size:.8rem;color:#166534;font-weight:600;display:flex;align-items:center;gap:.45rem">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          Kode OTP berhasil dikirimkan secara otomatis ke nomor WhatsApp {{ $phone }}!
        </div>
      @elseif(session('wa_url') || !empty($waUrl))
        <div style="margin-top:.85rem">
          <a href="{{ session('wa_url') ?? $waUrl }}" target="_blank" rel="noopener" class="btn wa block" style="font-size:.85rem;padding:.55rem 1rem">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
            Buka WhatsApp untuk Lihat / Kirim Pesan OTP
          </a>
        </div>
      @endif
    @endif
  </div>

  {{-- Testing helper badge --}}
  @if(session('dev_otp'))
    <div style="background:#eff6ff;border:1px dashed #3b82f6;border-radius:10px;padding:.85rem 1rem;margin-bottom:1.25rem;text-align:center">
      <span style="font-size:.78rem;color:#1e40af;font-weight:600;display:block">MODE UJI / SIMULASI LAB</span>
      <div style="font-size:1.6rem;font-weight:800;letter-spacing:6px;color:#1d4ed8;margin:.25rem 0;font-family:monospace">
        {{ session('dev_otp') }}
      </div>
      <span style="font-size:.74rem;color:#64748b">Kode di atas dapat langsung disalin ke form di bawah ini.</span>
    </div>
  @endif

  <form method="post" action="{{ route('verify.otp') }}" class="form">
    @csrf

    <label style="text-align:center">
      <span style="font-weight:600;display:block;margin-bottom:.5rem">Masukkan 6 Digit Kode OTP</span>
      <input type="text" name="otp" id="otpInput" maxlength="6" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" required placeholder="Contoh: 123456" autofocus style="font-size:1.45rem;letter-spacing:6px;text-align:center;font-weight:800;font-family:monospace;padding:.75rem">
    </label>

    <button type="submit" class="btn block" style="margin-top:.75rem;min-height:46px;font-size:.95rem">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
      Verifikasi Kode OTP
    </button>
  </form>

  {{-- Resend OTP Section --}}
  <div style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--border);text-align:center">
    <p style="font-size:.82rem;color:var(--muted);margin-bottom:.75rem">
      Tidak menerima kode OTP?
    </p>

    <form method="post" action="{{ route('resend.otp') }}" style="display:inline-block">
      @csrf
      @if($channel === 'whatsapp')
        <input type="hidden" name="custom_phone" value="{{ $phone }}">
      @endif
      <button type="submit" class="btn sm ghost" style="font-size:.82rem">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
        Kirim Ulang Kode OTP
      </button>
    </form>
  </div>

  <p class="center" style="margin-top:1.5rem;font-size:.85rem">
    <a href="{{ route('forgot') }}" style="display:inline-flex;align-items:center;gap:.35rem">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
      Ganti akun atau metode verifikasi
    </a>
  </p>
</section>
@endsection

