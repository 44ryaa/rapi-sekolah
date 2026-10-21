@extends('layouts.app')
@section('content')
<div class="page-head">
  <div>
    <h2 style="margin:0;font-size:1.25rem">Manajemen Pengguna Sistem</h2>
    <p class="muted" style="margin:.15rem 0 0">Kelola akun Admin, Waka Sarpras, Guru, dan PIC Ruangan.</p>
  </div>
  <button class="btn sm" data-user='{}'>
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Tambah Pengguna
  </button>
</div>

<div class="hint">
  <b>Daftar Role:</b> {{ $roles->pluck('role_name')->implode(', ') }}. Waka Sarpras memiliki wewenang untuk menyetujui (ACC) agenda dan perbaikan sarpras.
</div>

<div style="display:grid;gap:.85rem">
@foreach($users as $x)
@php
  $roleColor = match((int)$x->role_id) {
    1 => 'bad',    // Admin
    4 => 'warn',   // Waka
    2 => 'ok',     // PIC
    default => '', // Guru
  };
@endphp
<article class="card item">
  <div class="item-top">
    <div class="item-title-wrap">
      <div class="item-icon-squircle" style="background:var(--c-mocha);color:var(--c-cream)">
        {{ strtoupper(substr($x->fullname ?? $x->username, 0, 1)) }}
      </div>
      <div>
        <h3>{{ $x->fullname }}</h3>
        <span class="muted" style="font-size:.8rem">@<span>{{ $x->username }}</span></span>
      </div>
    </div>
    <span class="badge {{ $roleColor }}">{{ $x->role->role_name ?? 'User' }}</span>
  </div>

  <p class="meta">
    Nomor HP (WA): <b>{{ $x->phone_number ?: '-' }}</b>
    @if($x->role_id == 2) · Penanggung jawab <b>{{ (int)$x->room_count }}</b> ruangan @endif
  </p>

  <div class="actions">
    <button class="btn sm ghost" data-user="{{ json_encode(['id'=>$x->id,'username'=>$x->username,'fullname'=>$x->fullname,'phone_number'=>$x->phone_number,'role_id'=>$x->role_id]) }}">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Ubah
    </button>
    @if($x->id != auth()->id())
      <form method="post" action="{{ route('admin.users.delete') }}" data-confirm="Hapus akun pengguna {{ $x->fullname }}?">@csrf
        <input type="hidden" name="id" value="{{ $x->id }}">
        <button class="btn sm danger" type="submit">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          Hapus
        </button>
      </form>
    @endif
  </div>
</article>
@endforeach
</div>

{{-- Modal User Form --}}
<dialog id="userDialog" class="modal">
  <h2 id="userTitle" style="margin:0 0 1rem">Pengguna</h2>
  <form method="post" action="{{ route('admin.users.save') }}" class="form">@csrf
    <input type="hidden" name="id" id="uId" value="0">
    <label>Username (Akun Login)
      <input name="username" id="uUser" required pattern="[A-Za-z0-9_.]{3,50}" placeholder="Contoh: hendra.waka">
    </label>
    <label>Nama Lengkap &amp; Gelar
      <input name="fullname" id="uName" required placeholder="Contoh: Drs. H. Hendra M.Pd">
    </label>
    <label>Nomor WhatsApp Aktif
      <input name="phone_number" id="uPhone" inputmode="tel" required placeholder="Contoh: 081234567890">
    </label>
    <label>Role / Peran
      <select name="role_id" id="uRole">
        @foreach($roles as $r)
          <option value="{{ $r->id }}">{{ $r->role_name }}</option>
        @endforeach
      </select>
    </label>
    <label>Password
      <input type="password" name="password" id="uPass" autocomplete="new-password">
      <small class="muted" id="uPassHint">Minimal 6 karakter.</small>
    </label>
    <div class="actions" style="margin-top:1rem">
      <button class="btn" type="submit">Simpan Pengguna</button>
      <button class="btn ghost" type="button" data-close>Batal</button>
    </div>
  </form>
</dialog>
@endsection
