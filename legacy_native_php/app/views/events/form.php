<section class="card narrow">
  <h1>Ajukan peminjaman ruang</h1>
  <form method="post" action="<?= url('event/store') ?>" class="form" id="eventForm">
    <?= csrf_field() ?>
    <label>Nama kegiatan<input name="event_name" maxlength="150" required></label>
    <label>Ruang
      <select name="room_id" id="room" required>
        <option value="">Pilih ruang</option>
        <?php foreach ($rooms as $r) { ?><option value="<?= $r['id'] ?>"><?= e($r['room_name']) ?> (<?= (int) $r['capacity'] ?> orang<?= $r['location'] ? ', '.e($r['location']) : '' ?>)</option><?php } ?>
      </select>
    </label>
    <label>Tanggal<input type="date" name="date" id="date" min="<?= date('Y-m-d') ?>" required></label>
    <div class="row2">
      <label>Mulai<input type="time" name="start_time" id="start" required></label>
      <label>Selesai<input type="time" name="end_time" id="end" required></label>
    </div>
    <div id="slotBox" class="slotbox" hidden></div>
    <p id="slotError" class="field-error" role="alert" hidden></p>
    <button class="btn block" id="submitBtn"><?= role_is(ROLE_ADMIN) ? 'Simpan agenda' : 'Kirim pengajuan' ?></button>
  </form>
</section>
