<?php $u = auth(); ?>
<section class="card narrow">
  <h1>Profil saya</h1>
  <p class="muted">Username: <b><?= e($u['username']) ?></b> · Role: <b><?= e($u['role_name']) ?></b></p>
  <form method="post" action="<?= url('auth/profileSave') ?>" class="form">
    <?= csrf_field() ?>
    <label>Nama lengkap<input name="fullname" value="<?= e($u['fullname']) ?>" required></label>
    <label>Nomor HP (WhatsApp)<input name="phone_number" inputmode="tel" value="<?= e($u['phone_number']) ?>" required></label>
    <button class="btn block">Simpan profil</button>
  </form>
</section>
<section class="card narrow">
  <h2>Ganti password</h2>
  <form method="post" action="<?= url('auth/passwordSave') ?>" class="form">
    <?= csrf_field() ?>
    <label>Password saat ini<input type="password" name="current_password" required autocomplete="current-password"></label>
    <label>Password baru<input type="password" name="new_password" minlength="<?= cfg('app')['min_password'] ?>" required autocomplete="new-password"></label>
    <label>Ulangi password baru<input type="password" name="new_password2" required autocomplete="new-password"></label>
    <button class="btn block">Ganti password</button>
  </form>
</section>
