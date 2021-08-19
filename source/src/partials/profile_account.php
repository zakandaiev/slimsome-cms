<?php

$user_info = getUserInfo(null, $user_login);

?>

<div class="profile-content__info">
  <section class="block block_mb">
    <h2 class="block__title">Информация</h2>
    <div class="well appear-bottom">
      <form id="edit_user_info" method="post" class="form">
        <label>Логин</label>
        <input type="text" name="login" value="<?= $user_info["login"] ?>" placeholder="Логин на сайте" required>
        <label>Тип привязки</label>
        <select name="service_bind_type" required>
          <?php
            if($user_info["service_bind_type"] == 2) {
              echo '
                <option value="nick_pass">Ник + пароль</option>
                <option value="steam_pass" selected>Steam ID + пароль</option>
              ';
            } else {
              echo '
                <option value="nick_pass" selected>Ник + пароль</option>
                <option value="steam_pass">Steam ID + пароль</option>
              ';
            }
          ?>
        </select>
        <?php
          if($user_info["service_bind_type"] == 2) {
            echo '
              <div id="nick_pass" class="form-label" style="display:none;">
                <label>Ник на сервере</label>
                <input type="text" name="nick" value="'.$user_info["nick"].'" placeholder="Ваш ник на сервере">
              </div>
              <div id="steam_pass" class="form-label">
                <label>Steam ID</label>
                <input type="text" name="steam_id"  value="'.$user_info["steam_id"].'" placeholder="Ваш Steam ID">
              </div>
            ';
          } else {
            echo '
              <div id="nick_pass" class="form-label">
                <label>Ник на сервере</label>
                <input type="text" name="nick" value="'.$user_info["nick"].'" placeholder="Ваш ник на сервере">
              </div>
              <div id="steam_pass" class="form-label" style="display:none;">
                <label>Steam ID</label>
                <input type="text" name="steam_id"  value="'.$user_info["steam_id"].'" placeholder="Ваш Steam ID">
              </div>
            ';
          }
        ?>
        <label>Ваше Имя</label>
        <input type="text" name="name" value="<?= $user_info["name"] ?>" placeholder="Ваше Имя" required>
        <label>Ваш e-mail</label>
        <input type="email" name="email" value="<?= $user_info["email"] ?>" placeholder="Ваш e-mail" required>
        <input type="hidden" name="login_old" value="<?= $user_info["login"] ?>" required>
        <input type="hidden" name="uid_check" value="<?= $user_info["id"] ?>" required>
        <input type="hidden" name="pass_check" value="<?= $user_info["password"] ?>" required>
        <button type="submit" class="btn">Обновить</button>
      </form>
    </div>
  </section>
  <section class="block appear-bottom anim-delay-1">
    <h2 class="block__title">Изменить пароль</h2>
    <div class="well">
      <form id="edit_user_pass" method="post" class="form">
        <label><small>Пароль используется для привязки привилегии в игре и для логина на сайте</small></label>
        <label>Текущий пароль</label>
        <input type="password" name="current_pass" placeholder="Текущий пароль" required>
        <label>Новый пароль</label>
        <input type="password" name="new_pass" placeholder="Новый пароль" required>
        <input type="hidden" name="login_check" value="<?= $user_info["login"] ?>" required>
        <input type="hidden" name="uid_check" value="<?= $user_info["id"] ?>" required>
        <input type="hidden" name="pass_check" value="<?= $user_info["password"] ?>" required>
        <button type="submit" class="btn">Изменить</button>
      </form>
    </div>
  </section>
</div>
<div class="profile-content__services">
  <section class="block block_mb">
    <h2 class="block__title">Привилегии</h2>
    <div class="well appear-bottom">
      <?php
        if (isUserActive($user_info["id"], null)) {
          if ($user_info["service_nolimit"]) {
            echo '
              <p>У вас активна <b>безлимитная</b> привилегия <b>'.getServiceName($user_info["service_id"]).'</b> <img src="/img/smiles/cool.gif" alt="cool"></p>
            ';
          } else if (isset($user_info["service_end"]) && !empty($user_info["service_end"])) {
            echo '
              <p>У вас активна привилегия <b>'.getServiceName($user_info["service_id"]).'</b>, которая истекает через <b>'.dateDiff(time(), strtotime($user_info["service_end"])).'</b>.</p>
              <a href="profile?section=prolong" class="btn btn_cta btn_block">Продлить</a>
            ';
          } else {
            echo '
              <p>Привилегий нет.</p>
              <a href="buy" class="btn btn_cta btn_block">Купить</a>
            ';
          }
        } else {
          echo '
            <p>Привилегий нет.</p>
            <a href="buy" class="btn btn_cta btn_block">Купить</a>
          ';
        }
      ?>
    </div>
  </section>
  <? if ($user_info && $user_info["service_id"] && ($user_info["service_nolimit"] || time() < strtotime($user_info["service_end"])) ): ?>
    <section class="block appear-bottom anim-delay-1">
      <h2 class="block__title">Справка</h2>
      <div class="well">
        <h3>Активация привилегии</h3>
        <p>
          <span>1. Прописать в консоль игры:</span>
          <br>
          <span class="copy" data-copy="setinfo _pw <?= $user_info["password"] ?>" title="Нажми чтобы скопировать">setinfo _pw <?= $user_info["password"] ?><?= getSvg("img/icons/copy.svg") ?></span>
        </p>
        <p>2. В настройках игры должен быть установлен ваш ник к которому привязана привилегия.</p>
        <p>3. Для удобства рекомендуем забиндить кнопки.</p>

        <h3>Бинды для Админов/VIP</h3>
        <p>● меню оружий - <span class="copy" data-copy="bind v vipmenu" title="Нажми чтобы скопировать">bind v vipmenu<?= getSvg("img/icons/copy.svg") ?></span></p>
        <p>● меню админа - <span class="copy" data-copy="bind p amxmodmenu" title="Нажми чтобы скопировать">bind p amxmodmenu<?= getSvg("img/icons/copy.svg") ?></span></p>
      </div>
    </section>
  <? endif; ?>
</div>