@extends('layouts.app')
@section('content')
<section class="card narrow">
  <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.25rem">
    <div class="item-icon-squircle" style="width:46px;height:46px;border-radius:14px">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
    </div>
    <div>
      <h1 style="margin:0;font-size:1.45rem">Laporkan Kerusakan</h1>
      <p class="muted" style="margin:0;font-size:.84rem">Sampaikan kendala fasilitas sekolah secara cepat dan akurat.</p>
    </div>
  </div>

  <form method="post" action="{{ route('complaints.store') }}" enctype="multipart/form-data" class="form" id="complaintForm">
    @csrf

    <label>Pilih Ruangan Sarpras
      <select name="room_id" required>
        <option value="">-- Pilih Ruangan --</option>
        @foreach($rooms as $r)
          <option value="{{ $r->id }}" {{ (old('room_id', request('room_id')) == $r->id) ? 'selected' : '' }}>
            {{ $r->room_name }}{{ $r->location ? ' ('.$r->location.')' : '' }}
          </option>
        @endforeach
      </select>
    </label>

    <label>Rincian Kerusakan
      <textarea name="description" rows="3" minlength="10" required
        placeholder="Jelaskan bagian mana yang rusak... Contoh: Stop kontak korsleting dan proyektor tidak menyala.">{{ old('description') }}</textarea>
    </label>

    {{-- Real-time Camera vs File Upload Toggle --}}
    <div class="camera-widget">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem">
        <label style="margin:0;font-size:.9rem;font-weight:700">Bukti Foto Kerusakan</label>
        <span class="badge" id="cameraStatusBadge">Kamera Realtime</span>
      </div>

      <div class="camera-mode-toggle">
        <button type="button" class="camera-mode-btn active" id="tabCamMode">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
          Kamera Realtime
        </button>
        <button type="button" class="camera-mode-btn" id="tabFileMode">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          Unggah Berkas File
        </button>
      </div>

      {{-- Section 1: Real-time Live Camera Viewfinder --}}
      <div id="liveCamSection">
        <div class="camera-viewfinder-box" id="camBox">
          <video id="cameraVideo" autoplay playsinline muted></video>
          <div class="camera-live-badge">
            <span class="camera-live-dot"></span> LIVE CAM
          </div>
          {{-- Framing brackets --}}
          <div class="camera-guide-bracket bracket-tl"></div>
          <div class="camera-guide-bracket bracket-tr"></div>
          <div class="camera-guide-bracket bracket-bl"></div>
          <div class="camera-guide-bracket bracket-br"></div>
        </div>

        {{-- Captured Image Preview Container (Hidden initially) --}}
        <div id="capturedPreviewBox" style="display:none;margin-top:.5rem">
          <div class="camera-preview-wrap">
            <img id="capturedImgPreview" alt="Hasil Foto Kamera">
          </div>
          <div style="display:flex;gap:.5rem;justify-content:center;margin-top:.75rem">
            <button type="button" class="btn sm ghost" id="retakePhotoBtn">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
              Ambil Ulang
            </button>
            <span class="badge ok" style="padding:.4rem .85rem;font-size:.8rem">
              ✓ Foto Berhasil Diambil
            </span>
          </div>
        </div>

        {{-- Shutter and Switch Camera Controls --}}
        <div class="camera-actions-bar" id="cameraControlsBar">
          <button type="button" class="camera-alt-btn" id="switchCameraBtn" title="Ganti Kamera Depan/Belakang">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 16v4a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v4"/><polyline points="16 12 20 8 24 12"/></svg>
          </button>
          
          <button type="button" class="camera-shutter-btn" id="shutterBtn" title="Jepret Foto">
            <div class="camera-shutter-inner">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/></svg>
            </div>
          </button>

          <button type="button" class="camera-alt-btn" id="toggleTorchBtn" title="Segarkan Kamera">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
          </button>
        </div>

        <p class="muted center" style="font-size:.78rem;margin-top:.75rem">
          Arahkan kamera ke bagian kerusakan lalu tekan tombol jepret bundar di tengah.
        </p>

        {{-- Hidden canvas for capturing frame & hidden input for base64 data --}}
        <canvas id="cameraCanvas" style="display:none"></canvas>
        <input type="hidden" name="camera_photo_data" id="cameraPhotoData">
      </div>

      {{-- Section 2: Standard File Upload Fallback --}}
      <div id="fileUploadSection" style="display:none">
        <label style="margin:0">
          <input type="file" name="photo" id="photo" accept="image/*" capture="environment">
          <small class="muted">Pilih file foto dari perangkat Anda (maksimal 5 MB).</small>
        </label>
        <img id="preview" class="preview" alt="Pratinjau foto bukti" hidden style="margin-top:.75rem">
      </div>
    </div>

    <button type="submit" class="btn block" style="min-height:48px;font-size:1rem;margin-top:.5rem">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
      Kirim Pengaduan Kerusakan
    </button>
  </form>
</section>

@push('scripts')
<script>
(function() {
  const tabCamMode       = document.getElementById('tabCamMode');
  const tabFileMode      = document.getElementById('tabFileMode');
  const liveCamSection   = document.getElementById('liveCamSection');
  const fileUploadSection= document.getElementById('fileUploadSection');
  const statusBadge      = document.getElementById('cameraStatusBadge');

  const video            = document.getElementById('cameraVideo');
  const canvas           = document.getElementById('cameraCanvas');
  const shutterBtn       = document.getElementById('shutterBtn');
  const switchCameraBtn  = document.getElementById('switchCameraBtn');
  const retakePhotoBtn   = document.getElementById('retakePhotoBtn');
  const camBox           = document.getElementById('camBox');
  const controlsBar      = document.getElementById('cameraControlsBar');
  const capturedBox      = document.getElementById('capturedPreviewBox');
  const capturedImg      = document.getElementById('capturedImgPreview');
  const cameraPhotoData  = document.getElementById('cameraPhotoData');

  let currentStream = null;
  let useFacingMode = 'environment'; // default to rear camera on mobile

  // Switch between Realtime Camera & File upload
  function activateCamTab() {
    tabCamMode.classList.add('active');
    tabFileMode.classList.remove('active');
    liveCamSection.style.display = 'block';
    fileUploadSection.style.display = 'none';
    statusBadge.textContent = 'Kamera Realtime';
    startCamera();
  }

  function activateFileTab() {
    tabFileMode.classList.add('active');
    tabCamMode.classList.remove('active');
    liveCamSection.style.display = 'none';
    fileUploadSection.style.display = 'block';
    statusBadge.textContent = 'Unggah Berkas';
    stopCamera();
    cameraPhotoData.value = '';
  }

  tabCamMode.addEventListener('click', activateCamTab);
  tabFileMode.addEventListener('click', activateFileTab);

  // Start video stream
  async function startCamera() {
    stopCamera();
    try {
      const constraints = {
        video: {
          facingMode: { ideal: useFacingMode },
          width: { ideal: 1280 },
          height: { ideal: 720 }
        },
        audio: false
      };
      currentStream = await navigator.mediaDevices.getUserMedia(constraints);
      video.srcObject = currentStream;
      await video.play();
      camBox.style.display = 'flex';
    } catch (err) {
      console.warn('Camera access denied or unavailable:', err);
      statusBadge.textContent = 'Kamera Tidak Tersedia';
      // If camera unavailable, automatically switch to File Upload mode
      if (typeof window.showToast === 'function') {
        window.showToast('Kamera tidak dapat diakses. Mengalihkan ke opsi file upload.', 'bad');
      }
      activateFileTab();
    }
  }

  // Stop video stream
  function stopCamera() {
    if (currentStream) {
      currentStream.getTracks().forEach(track => track.stop());
      currentStream = null;
    }
  }

  // Switch Camera (Flip front / back)
  switchCameraBtn?.addEventListener('click', () => {
    useFacingMode = (useFacingMode === 'environment') ? 'user' : 'environment';
    startCamera();
  });

  // Capture Photo (Snap)
  shutterBtn?.addEventListener('click', () => {
    if (!video.videoWidth) return;
    
    // Draw current frame to hidden canvas
    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    // Convert to JPEG Data URL
    const dataUrl = canvas.toDataURL('image/jpeg', 0.88);
    cameraPhotoData.value = dataUrl;
    capturedImg.src = dataUrl;

    // Show preview and hide live feed
    camBox.style.display = 'none';
    controlsBar.style.display = 'none';
    capturedBox.style.display = 'block';

    if (typeof window.showToast === 'function') {
      window.showToast('Foto berhasil dijepret!', 'ok');
    }
  });

  // Retake Photo
  retakePhotoBtn?.addEventListener('click', () => {
    cameraPhotoData.value = '';
    capturedBox.style.display = 'none';
    camBox.style.display = 'flex';
    controlsBar.style.display = 'flex';
  });

  // Start camera on page load if visible
  if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
    startCamera();
  } else {
    activateFileTab();
  }

  // Cleanup camera stream when navigating away
  window.addEventListener('beforeunload', stopCamera);
})();
</script>
@endpush
@endsection
