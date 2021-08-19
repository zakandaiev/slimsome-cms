<? require_once("partials/header.php"); ?>

<section class="block block_slim">
  <h2 class="block__title">Восстановление доступа</h2>
  <div class="well appear-bottom">
    <form id="restore_form" class="form" method="post">
      <label>Укажите e-mail</label>
      <input type="email" name="email" placeholder="Ваш e-mail на сайте" required>
      <button class="btn btn_primary" type="submit">Восстановить</button>
    </form>
  </div>
</section>

<? require_once("partials/footer.php"); ?>