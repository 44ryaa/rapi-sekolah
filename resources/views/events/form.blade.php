@extends('layouts.app')
@section('content')
<section class="card narrow">
  <h1>Ajukan peminjaman ruang</h1>
  <p class="muted">Pengajuan hanya bisa dilakukan <b>minimal H-1</b> (hari ini tidak bisa).</p>

  <form method="post" action="{{ route('events.store') }}" class="form" id="eventForm">
    @csrf

    {{-- Nama kegiatan --}}
    <label>Nama kegiatan
      <input name="event_name" maxlength="150" required
        value="{{ old('event_name') }}"
        placeholder="Contoh: Rapat OSIS, Pelatihan Guru, Ujian Praktek">
    </label>

    {{-- Ruang --}}
    <label>Ruang
      <select name="room_id" id="room" required>
        <option value="">— Pilih ruang —</option>
        @foreach($rooms as $r)
          <option value="{{ $r->id }}" {{ old('room_id')==$r->id ? 'selected' : '' }}>
            {{ $r->room_name }}
            ({{ (int)$r->capacity }} orang{{ $r->location ? ', '.$r->location : '' }})
          </option>
        @endforeach
      </select>
    </label>

    {{-- Tanggal: minimal besok --}}
    <label>Tanggal kegiatan
      <input type="date" name="date" id="date"
        min="{{ $minDate }}"
        required
        value="{{ old('date') }}">
      <small class="muted">Pengajuan minimal 1 hari sebelum kegiatan (H-1). Peminjaman tidak tersedia pada hari Sabtu dan Minggu.</small>
      <div id="weekendWarning" style="display:none;margin-top:.4rem;padding:.5rem .75rem;background:#fee2e2;border:1px solid #f87171;color:#b91c1c;border-radius:var(--radius-sm);font-size:.82rem;font-weight:600">
        Hari Sabtu dan Minggu tidak dapat dipilih untuk peminjaman ruangan. Silakan pilih hari Senin s.d. Jumat.
      </div>
    </label>

    {{-- Sesi --}}
    <div id="sessionBox">
      <div class="row2">
        <label id="startWrapper">Sesi mulai
          <select name="session_start" id="sesStart" required>
            <option value="">— Pilih sesi —</option>
            @foreach($sessions as $num => $s)
              <option value="{{ $num }}" {{ old('session_start')==$num ? 'selected' : '' }}>
                {{ $s['label'] }}
              </option>
            @endforeach
          </select>
        </label>
        <label id="endWrapper">Sesi selesai
          <select name="session_end" id="sesEnd" required>
            <option value="">— Pilih sesi selesai —</option>
            @foreach($sessions as $num => $s)
              @if($num !== 6)
                <option value="{{ $num }}" {{ old('session_end')==$num ? 'selected' : '' }}>
                  {{ $s['label'] }}
                </option>
              @endif
            @endforeach
          </select>
        </label>
      </div>
      <div id="fullSessionNote" style="display:none;margin-top:.4rem;padding:.65rem .85rem;background:var(--c-cream-soft);border-radius:var(--radius-sm);border-left:3.5px solid var(--accent);font-size:.84rem;color:var(--ink)">
        <b>Sesi Penuh Aktif:</b> Ruangan akan dipesan seharian penuh dari <b>07:00 hingga 16:00</b>. Sesi selesai tidak perlu dipilih.
      </div>
      <small class="muted" id="sessionHint">Pilih "Sesi Penuh" untuk memesan seharian (07:00–16:00).</small>
    </div>

    {{-- Status ketersediaan sesi --}}
    <div id="slotBox" class="slotbox" hidden></div>
    <p id="slotError" class="field-error" role="alert" hidden></p>

    <button class="btn block" id="submitBtn">
      {{ (auth()->user()->isAdmin() || auth()->user()->isWaka()) ? 'Simpan & Setujui Langsung' : 'Kirim Pengajuan Agenda' }}
    </button>
  </form>
</section>

<script>
// Sesi data dari server
const SESSIONS = @json($sessions);

const formEl         = document.getElementById('eventForm');
const roomEl         = document.getElementById('room');
const dateEl         = document.getElementById('date');
const sesStart       = document.getElementById('sesStart');
const sesEnd         = document.getElementById('sesEnd');
const slotBox        = document.getElementById('slotBox');
const slotError      = document.getElementById('slotError');
const weekendWarning = document.getElementById('weekendWarning');

// Cek apakah tanggal jatuh pada akhir pekan (Sabtu=6 atau Minggu=0)
function checkWeekend() {
  const dateVal = dateEl.value;
  if (!dateVal) {
    if (weekendWarning) weekendWarning.style.display = 'none';
    dateEl.setCustomValidity('');
    return true;
  }

  const parts = dateVal.split('-');
  if (parts.length === 3) {
    const d = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
    const day = d.getDay(); // 0 = Minggu, 6 = Sabtu
    if (day === 0 || day === 6) {
      if (weekendWarning) weekendWarning.style.display = 'block';
      dateEl.setCustomValidity('Peminjaman ruangan tidak tersedia pada hari Sabtu dan Minggu.');
      if (slotBox) {
        slotBox.hidden = false;
        slotBox.innerHTML = '<p style="margin:0;color:var(--bad);font-weight:600">Peminjaman ruangan tidak tersedia pada hari Sabtu dan Minggu.</p>';
      }
      return false;
    }
  }

  if (weekendWarning) weekendWarning.style.display = 'none';
  dateEl.setCustomValidity('');
  return true;
}

// Jika pilih Sesi Penuh (6), sesi selesai tidak usah digunakan
function handleSessionChange() {
  const isFull = sesStart.value == '6';
  const endWrapper = document.getElementById('endWrapper');
  const fullNote = document.getElementById('fullSessionNote');
  const sessionHint = document.getElementById('sessionHint');

  if (isFull) {
    if (endWrapper) endWrapper.style.display = 'none';
    if (fullNote) fullNote.style.display = 'block';
    if (sessionHint) sessionHint.style.display = 'none';
    sesEnd.required = false;
    sesEnd.value = '';
  } else {
    if (endWrapper) endWrapper.style.display = '';
    if (fullNote) fullNote.style.display = 'none';
    if (sessionHint) sessionHint.style.display = '';
    sesEnd.required = true;
  }
}

sesStart.addEventListener('change', () => {
  handleSessionChange();
  loadSlots();
});
sesEnd.addEventListener('change', loadSlots);

// Initial state check on load
checkWeekend();
handleSessionChange();

function loadSlots() {
  if (!checkWeekend()) return;
  const room = roomEl.value;
  const date = dateEl.value;
  if (!room || !date) return;

  fetch(`{{ route('events.slots') }}?room_id=${room}&date=${date}`)
    .then(r => r.json())
    .then(data => {
      if (data.weekend || data.error) {
        slotBox.hidden = false;
        slotBox.innerHTML = `<p style="margin:0;color:var(--bad);font-weight:600">${data.error || 'Peminjaman ruangan tidak tersedia pada akhir pekan.'}</p>`;
        return;
      }
      if (!data.sessions) return;
      slotBox.hidden = false;
      slotBox.innerHTML = '<p style="margin:0 0 .4rem;font-weight:600">Status sesi di ruang ini:</p><ul style="margin:0;padding:0;list-style:none;display:grid;gap:.35rem">';
      for (const [num, s] of Object.entries(data.sessions)) {
        const tag = s.taken 
          ? '<span class="badge bad" style="font-size:.65rem;padding:.15rem .45rem">Terisi</span>' 
          : '<span class="badge ok" style="font-size:.65rem;padding:.15rem .45rem">Tersedia</span>';
        const extra = s.taken ? ` <span style="color:var(--bad);font-size:.82em">(${s.by})</span>` : '';
        slotBox.innerHTML += `<li style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap">${tag} <span>${s.label}</span>${extra}</li>`;
      }
      slotBox.innerHTML += '</ul>';
    })
    .catch(() => {});
}

roomEl.addEventListener('change', loadSlots);
dateEl.addEventListener('change', () => {
  checkWeekend();
  loadSlots();
});
dateEl.addEventListener('input', checkWeekend);

if (formEl) {
  formEl.addEventListener('submit', (e) => {
    if (!checkWeekend()) {
      e.preventDefault();
      dateEl.reportValidity();
      return false;
    }
  });
}
</script>
@endsection
