<div class="page-head"><h1>Ruang &amp; PIC</h1><button class="btn sm" data-room='{}'>Tambah ruang</button></div>
<p class="hint">Satu PIC dapat memegang beberapa ruangan. PIC hanya melihat pengaduan di ruangan yang ditugaskan kepadanya.</p>
<?php foreach ($rooms as $r) { ?>
<article class="card item">
  <div class="item-top"><h3><?= e($r['room_name']) ?></h3><span class="badge <?= $r['is_active'] ? 'ok' : '' ?>"><?= $r['is_active'] ? 'Aktif' : 'Nonaktif' ?></span></div>
  <p class="meta"><?= (int) $r['capacity'] ?> orang · <?= e($r['location'] ?: '-') ?> · PIC: <b><?= e($r['pic_name'] ?? 'belum ditunjuk') ?></b></p>
  <div class="actions"><button class="btn sm ghost" data-room="<?= e(json_encode(['id' => $r['id'], 'room_name' => $r['room_name'], 'capacity' => $r['capacity'], 'location' => $r['location'], 'pic_id' => $r['pic_id'], 'is_active' => $r['is_active']])) ?>">Ubah</button></div>
</article>
<?php } ?>
<dialog id="roomDialog" class="modal">
  <h2 id="roomTitle">Ruang</h2>
  <form method="post" action="<?= url('admin/roomSave') ?>" class="form">
    <?= csrf_field() ?><input type="hidden" name="id" id="rId" value="0">
    <label>Nama ruang<input name="room_name" id="rName" required></label>
    <div class="row2"><label>Kapasitas<input type="number" name="capacity" id="rCap" min="0" value="0"></label>
    <label>Lokasi<input name="location" id="rLoc"></label></div>
    <label>PIC Ruang<select name="pic_id" id="rPic"><option value="0">Belum ditunjuk</option><?php foreach ($pics as $p) { ?><option value="<?= $p['id'] ?>"><?= e($p['fullname']) ?></option><?php } ?></select></label>
    <label class="check"><input type="checkbox" name="is_active" id="rActive" checked> Ruang aktif (bisa dipinjam)</label>
    <div class="actions"><button class="btn">Simpan</button><button class="btn ghost" type="button" data-close>Batal</button></div>
  </form>
</dialog>
