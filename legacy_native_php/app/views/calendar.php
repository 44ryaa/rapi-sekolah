<section class="card cal">
  <div class="cal-head">
    <button class="btn ghost sm" id="prev" aria-label="Bulan sebelumnya">‹</button>
    <h1 id="monthLabel">Kalender</h1>
    <button class="btn ghost sm" id="next" aria-label="Bulan berikutnya">›</button>
  </div>
  <div class="cal-sub"><button class="link" id="today">Ke hari ini</button>
    <?php if (role_is(ROLE_ADMIN, ROLE_GURU)) { ?><a class="btn sm" href="<?= url('event/create') ?>">Ajukan agenda</a><?php } ?></div>
  <div class="grid dow"><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span></div>
  <div class="grid days" id="days" aria-live="polite"></div>
</section>
<section class="card">
  <h2 id="dayTitle">Agenda</h2>
  <ul class="agenda" id="dayList"></ul>
</section>
<p class="hint">Kalender ini hanya untuk dilihat. Garis berwarna menandai ruangan; agenda berwarna pucat masih menunggu persetujuan Waka.</p>
