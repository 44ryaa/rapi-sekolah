<div class="page-head"><h1>Pengguna</h1><button class="btn sm" data-user='{}'>Tambah pengguna</button></div>
<p class="hint">Peran: <?= e(implode(', ', array_column($roles, 'role_name'))) ?>. Siswa dan tamu tidak memerlukan akun.</p>
<?php foreach ($users as $x) { ?>
<article class="card item">
  <div class="item-top"><h3><?= e($x['fullname']) ?></h3><span class="badge"><?= e($x['role_name']) ?></span></div>
  <p class="meta"><?= e($x['username']) ?> · <?= e($x['phone_number']) ?><?= $x['role_id'] == ROLE_PIC ? ' · '.(int) $x['room_count'].' ruangan' : '' ?></p>
  <div class="actions">
    <button class="btn sm ghost" data-user="<?= e(json_encode(['id' => $x['id'], 'username' => $x['username'], 'fullname' => $x['fullname'], 'phone_number' => $x['phone_number'], 'role_id' => $x['role_id']])) ?>">Ubah</button>
    <?php if ($x['id'] != auth()['id']) { ?>
    <form method="post" action="<?= url('admin/userDelete') ?>" data-confirm="Hapus pengguna <?= e($x['fullname']) ?>?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $x['id'] ?>"><button class="btn sm danger">Hapus</button></form>
    <?php } ?>
  </div>
</article>
<?php } ?>
<dialog id="userDialog" class="modal">
  <h2 id="userTitle">Pengguna</h2>
  <form method="post" action="<?= url('admin/userSave') ?>" class="form">
    <?= csrf_field() ?><input type="hidden" name="id" id="uId" value="0">
    <label>Username<input name="username" id="uUser" required pattern="[A-Za-z0-9_.]{3,50}"></label>
    <label>Nama lengkap<input name="fullname" id="uName" required></label>
    <label>Nomor HP (WhatsApp)<input name="phone_number" id="uPhone" inputmode="tel" required></label>
    <label>Role<select name="role_id" id="uRole"><?php foreach ($roles as $r) { ?><option value="<?= $r['id'] ?>"><?= e($r['role_name']) ?></option><?php } ?></select></label>
    <label>Password<input type="password" name="password" id="uPass" autocomplete="new-password"><small class="muted" id="uPassHint"></small></label>
    <div class="actions"><button class="btn">Simpan</button><button class="btn ghost" type="button" data-close>Batal</button></div>
  </form>
</dialog>
