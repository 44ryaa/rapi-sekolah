<section class="card narrow">
  <h1>Masuk</h1>
  <p class="muted">Guru, organisasi siswa, PIC ruang, dan Waka masuk lewat halaman yang sama.</p>
  <form method="post" action="<?= url('auth/login') ?>" class="form" autocomplete="on">
    <?= csrf_field() ?>
    <label>Username<input name="username" required autocomplete="username" autofocus></label>
    <label>Password<input type="password" name="password" required autocomplete="current-password"></label>
    <button class="btn block">Masuk</button>
  </form>
  <p class="center"><a href="<?= url('auth/forgot') ?>">Lupa password?</a></p>
  <p class="center muted">Siswa atau tamu tidak perlu masuk. <a href="<?= url('calendar/index') ?>">Lihat kalender</a></p>
</section>
