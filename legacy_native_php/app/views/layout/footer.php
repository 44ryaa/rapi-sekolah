</main>
<?php if (! empty($unread)) { ?>
<dialog id="notifDialog" class="modal" aria-labelledby="nt">
  <h2 id="nt">Pemberitahuan untuk Anda</h2>
  <ul class="notif-list">
    <?php foreach ($unread as $n) { ?><li><?= e($n['message']) ?><time><?= e(tgl($n['created_at'])).' '.e(jam(substr($n['created_at'], 11))) ?></time></li><?php } ?>
  </ul>
  <form method="post" action="<?= url('notification/read') ?>"><?= csrf_field() ?><button class="btn block">Mengerti</button></form>
</dialog>
<?php } ?>
<script>window.RAPI = { api: <?= json_encode(url('calendar/api')) ?>, slots: <?= json_encode(url('event/slots')) ?> };</script>
<script src="<?= asset('assets/js/app.js') ?>"></script>
<?php if (($route ?? '') === 'calendar/index') { ?><script src="<?= asset('assets/js/calendar.js') ?>"></script><?php } ?>
</body>
</html>
