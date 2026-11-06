<?php $admin = role_is(ROLE_ADMIN);
$pic = role_is(ROLE_PIC);
$guru = role_is(ROLE_GURU);
$uid = (int) auth()['id'];
$cls = ['Belum Diperbaiki' => 'bad', 'Menunggu Spare Part' => 'warn', 'Sudah Diperbaiki' => 'ok']; ?>
<div class="page-head"><h1><?= $pic ? 'Pengaduan ruangan saya' : 'Pengaduan kerusakan' ?></h1>
  <?php if (role_is(ROLE_GURU)) { ?><a class="btn sm" href="<?= url('complaint/create') ?>">Laporkan kerusakan</a><?php } ?></div>
<?php if ($admin) { ?><p class="hint">Tombol peringatan WhatsApp aktif bila pengaduan masih "Belum Diperbaiki" lebih dari <?= (int) $limitH ?> jam.</p><?php } ?>
<?php if (! $complaints) { ?><section class="card center"><p><?= $pic ? 'Belum ada pengaduan di ruangan Anda.' : 'Belum ada pengaduan.' ?></p></section><?php } ?>
<?php foreach ($complaints as $c) { ?>
<article class="card item">
  <div class="item-top"><h3><?= e($c['room_name']) ?></h3><span class="badge <?= $cls[$c['status_repair']] ?>"><?= e($c['status_repair']) ?></span></div>
  <p><?= nl2br(e($c['description'])) ?></p>
  <?php if ($c['photo_path']) { ?><a href="<?= asset($c['photo_path']) ?>" target="_blank" rel="noopener"><img class="thumb" src="<?= asset($c['photo_path']) ?>" alt="Foto kerusakan <?= e($c['room_name']) ?>" loading="lazy"></a><?php } ?>
  <p class="meta">Dilapor <?= e($c['reporter']) ?> · <?= e(tgl($c['reported_at'])) ?> (<?= (int) $c['age_h'] ?> jam lalu)<?= $admin ? ' · PIC: '.e($c['pic_name'] ?? 'belum ditunjuk') : '' ?></p>
  <?php if ($c['repair_note']) { ?><p class="note">Catatan PIC: <?= e($c['repair_note']) ?></p><?php } ?>

  <?php if ($pic) { ?>
  <form method="post" action="<?= url('complaint/status') ?>" class="form inline">
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= $c['id'] ?>">
    <label>Status penanganan<select name="status_repair"><?php foreach ($statuses as $s) { ?><option <?= $s === $c['status_repair'] ? 'selected' : '' ?>><?= e($s) ?></option><?php } ?></select></label>
    <label>Catatan perkembangan<input name="repair_note" value="<?= e($c['repair_note']) ?>" placeholder="Contoh: Spare part dipesan, tiba Jumat."></label>
    <button class="btn sm">Simpan status</button>
  </form>
  <?php } ?>

  <?php if ($admin && $c['status_repair'] === 'Belum Diperbaiki') { ?>
    <div class="actions">
    <?php if ($c['overdue'] && $c['wa']) { ?>
      <a class="btn wa" href="<?= e($c['wa']) ?>" target="_blank" rel="noopener">Kirim peringatan via WhatsApp</a>
    <?php } else { ?>
      <button class="btn wa" disabled><?= $c['wa'] ? 'Peringatan aktif setelah '.(int) $limitH.' jam' : 'PIC belum punya nomor' ?></button>
    <?php } ?>
    </div>
  <?php } ?>

  <?php if ($admin || ($guru && (int) $c['reporter_id'] === $uid)) { ?>
  <form method="post" action="<?= url('complaint/delete') ?>" class="form inline" onsubmit="return confirm('Yakin ingin menghapus pengaduan ini? Data yang dihapus tidak dapat dikembalikan.')">
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= $c['id'] ?>">
    <button type="submit" class="btn sm danger">Hapus laporan</button>
  </form>
  <?php } ?>
</article>
<?php } ?>

