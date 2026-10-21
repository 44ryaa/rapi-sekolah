@extends('layouts.app')
@section('content')
<div style="max-width:540px;margin:0 auto;display:grid;gap:1.25rem">
  <section class="card" style="padding:1.75rem">
    <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem">
      <div class="user-avatar-sq" style="width:52px;height:52px;font-size:1.35rem;border-radius:14px">
        {{ strtoupper(substr(auth()->user()->fullname ?? auth()->user()->username, 0, 1)) }}
      </div>
      <div>
        <h2 style="margin:0;font-size:1.25rem">{{ auth()->user()->fullname ?? auth()->user()->username }}</h2>
        <span class="badge" style="margin-top:4px">{{ auth()->user()->role->role_name ?? 'User' }}</span>
        <span class="muted" style="font-size:.82rem;margin-left:4px">(@<span>{{ auth()->user()->username }}</span>)</span>
      </div>
    </div>

    <form method="post" action="{{ route('profile.save') }}" class="form">
      @csrf
      <label>Nama Lengkap
        <input name="fullname" value="{{ old('fullname', auth()->user()->fullname) }}" required>
      </label>
      <label>Nomor HP (WhatsApp)
        <input name="phone_number" inputmode="tel" value="{{ old('phone_number', auth()->user()->phone_number) }}" required>
      </label>
      <button class="btn block" style="margin-top:.5rem">Simpan Perubahan Profil</button>
    </form>
  </section>

  <section class="card" style="padding:1.75rem">
    <h3 style="font-size:1.15rem;margin-bottom:.5rem">Perbarui Kata Sandi</h3>
    <p class="muted" style="margin-bottom:1.25rem">Gunakan kata sandi yang aman minimal 6 karakter.</p>

    <form method="post" action="{{ route('profile.password') }}" class="form">
      @csrf
      <label>Password Saat Ini
        <input type="password" name="current_password" required autocomplete="current-password" placeholder="••••••••">
      </label>
      <label>Password Baru
        <input type="password" name="new_password" minlength="6" required autocomplete="new-password" placeholder="Minimal 6 karakter">
      </label>
      <label>Ulangi Password Baru
        <input type="password" name="new_password2" required autocomplete="new-password" placeholder="Ketik ulang password baru">
      </label>
      <button class="btn block ghost" style="margin-top:.5rem">Ganti Password</button>
    </form>
  </section>
</div>
@endsection
