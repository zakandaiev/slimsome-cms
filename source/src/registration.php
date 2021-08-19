<? require_once("partials/header.php"); ?>

<section class="block block_slim">
  <h2 class="block__title">Регистрация</h2>
  <div class="well appear-bottom">
    <form id="registration_form" class="form" method="post">
      <label>Укажите логин на сайте</label>
      <input type="text" name="login" placeholder="Ваш логин" required>
      <label>
        Придумайте пароль
        <br><small>Пароль будет использоваться для привязки привилегии в игре и для логина на сайте</small>
      </label>
      <input type="password" name="password" placeholder="Ваш пароль" required>
      <label>Укажите e-mail</label>
      <input type="email" name="email" placeholder="Ваш e-mail" required>
      <label>Укажите ваше имя</label>
      <input type="text" name="name" placeholder="Ваш имя" required>
      <label>Укажите ник</label>
      <input type="text" name="nick" placeholder="Ваш ник на сервере" required>
      <label>Согласен с <a href="processing-of-personal-data" target="_blank">политикой обработки персональных данных</a></label>
      <label class="switcher">
        <input type="checkbox" name="policy_data" checked>
        <span class="slider round"></span>
      </label>
      <label>Согласен с <a href="privacy-policy" target="_blank">политикой конфиденциальности</a></label>
      <label class="switcher">
        <input type="checkbox" name="policy_privacy" checked>
        <span class="slider round"></span>
      </label>
      <button class="btn btn_primary" type="submit">Зарегистрироваться</button>
    </form>
  </div>
</section>

<? require_once("partials/footer.php"); ?>