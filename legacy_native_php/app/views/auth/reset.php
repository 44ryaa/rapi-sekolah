<section class="card narrow">
  <h1>Buat password baru</h1>
  <p class="muted">Tautan ini berlaku 30 menit dan hanya bisa dipakai sekali.</p>
  <form method="post" action="<?= url('auth/reset') ?>" class="form">
    <?= csrf_field() ?><input type="hidden" name="token" value="<?= e($token) ?>">
    <label>Password baru<input type="password" name="password" minlength="<?= cfg('app')['min_password'] ?>" required autocomplete="new-password"></label>
    <label>Ulangi password baru<input type="password" name="password2" minlength="<?= cfg('app')['min_password'] ?>" required autocomplete="new-password"></label>
    <button class="btn block">Simpan password</button>
  </form>
</section>
