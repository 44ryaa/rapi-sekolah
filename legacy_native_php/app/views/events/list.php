<?php $admin = role_is(ROLE_ADMIN); ?>
<div class="page-head"><h1><?= $admin ? 'Semua agenda' : 'Agenda saya' ?></h1><a class="btn sm" href="<?= url('event/create') ?>">Ajukan agenda</a></div>
<?php if (! $events) { ?><section class="card center"><p>Belum ada agenda. Ajukan peminjaman ruang pertama Anda.</p><a class="btn" href="<?= url('event/create') ?>">Ajukan agenda</a></section><?php } ?>
<?php foreach ($events as $ev) {
    $cls = ['Diajukan' => 'warn', 'Disetujui' => 'ok', 'Ditolak' => 'bad'][$ev['status']]; ?>
<article class="card item">
  <div class="item-top"><h3><?= e($ev['event_name']) ?></h3><span class="badge <?= $cls ?>"><?= e($ev['status']) ?></span></div>
  <p class="meta"><?= e(tgl($ev['date'])) ?> · <?= e(jam($ev['start_time'])) ?>–<?= e(jam($ev['end_time'])) ?> · <b><?= e($ev['room_name']) ?></b><?= $admin ? ' · '.e($ev['requester']) : '' ?></p>
  <?php if ($ev['move_reason']) { ?><p class="note">Ruang dipindahkan oleh Waka. Alasan: <?= e($ev['move_reason']) ?></p><?php } ?>
  <?php if ($admin && $ev['status'] !== 'Ditolak') { ?>
  <div class="actions">
    <?php if ($ev['status'] === 'Diajukan') { ?>
      <form method="post" action="<?= url('event/review') ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $ev['id'] ?>"><input type="hidden" name="decision" value="approve"><button class="btn sm">Setujui</button></form>
      <form method="post" action="<?= url('event/review') ?>" data-confirm="Tolak pengajuan ini?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $ev['id'] ?>"><input type="hidden" name="decision" value="reject"><button class="btn sm danger">Tolak</button></form>
    <?php } ?>
    <button class="btn sm ghost" type="button" data-move="<?= $ev['id'] ?>" data-name="<?= e($ev['event_name']) ?>" data-room="<?= $ev['room_id'] ?>">Pindah ruang</button>
  </div>
  <?php } ?>
</article>
<?php } ?>

<?php if ($admin) { ?>
<dialog id="moveDialog" class="modal">
  <h2>Pindah ruang agenda</h2>
  <p class="muted" id="moveName"></p>
  <form method="post" action="<?= url('event/move') ?>" class="form">
    <?= csrf_field() ?><input type="hidden" name="id" id="moveId">
    <label>Ruang tujuan<select name="new_room_id" id="moveRoom" required>
      <?php foreach ($rooms as $r) { ?><option value="<?= $r['id'] ?>"><?= e($r['room_name']) ?></option><?php } ?></select></label>
    <label>Alasan pemindahan<textarea name="reason" rows="3" minlength="5" required placeholder="Contoh: Aula dipakai untuk rapat mendadak dinas."></textarea></label>
    <p class="muted">Peminjam akan menerima notifikasi berisi ruang baru dan alasan ini.</p>
    <div class="actions"><button class="btn">Pindahkan</button><button class="btn ghost" type="button" data-close>Batal</button></div>
  </form>
</dialog>
<?php } ?>
