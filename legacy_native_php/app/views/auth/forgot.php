<section class="card narrow">
  <h1>Lupa password</h1>
  <p class="muted">Masukkan username dan nomor HP yang terdaftar di akun Anda. Jika cocok, Anda bisa langsung membuat password baru.</p>
  <form method="post" action="<?= url('auth/forgot') ?>" class="form">
    <?= csrf_field() ?>
    <label>Username<input name="username" required autocomplete="username"></label>
    <label>Nomor HP terdaftar<input name="phone" inputmode="tel" placeholder="0812xxxxxxx" required autocomplete="tel"></label>
    <button class="btn block">Verifikasi</button>
  </form>
  <p class="center"><a href="<?= url('auth/login') ?>">Kembali ke halaman masuk</a></p>
</section>
