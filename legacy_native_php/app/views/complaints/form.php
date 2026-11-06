<section class="card narrow">
  <h1>Laporkan kerusakan</h1>
  <form method="post" action="<?= url('complaint/store') ?>" enctype="multipart/form-data" class="form">
    <?= csrf_field() ?>
    <label>Ruang
      <select name="room_id" required><option value="">Pilih ruang</option>
        <?php foreach ($rooms as $r) { ?><option value="<?= $r['id'] ?>"><?= e($r['room_name']) ?><?= $r['location'] ? ' ('.e($r['location']).')' : '' ?></option><?php } ?></select></label>
    <label>Apa yang rusak?<textarea name="description" rows="4" minlength="10" required placeholder="Contoh: Proyektor tidak menyala, lampu indikator berkedip merah."></textarea></label>
    <label>Foto bukti
      <input type="file" name="photo" id="photo" accept="image/*" capture="environment" required>
      <small class="muted">Di ponsel, kamera akan terbuka langsung. Foto otomatis dikecilkan agar hemat kuota.</small>
    </label>
    <img id="preview" class="preview" alt="Pratinjau foto bukti" hidden>
    <button class="btn block">Kirim pengaduan</button>
  </form>
</section>
