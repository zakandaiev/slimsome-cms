<?php

require_once("partials/header.php");

$db_services_buyable = require_once("core/db_services_buyable.php");

$db_payments = json_decode($GLOBALS["payments"], true);

foreach ($db_payments as $key => $rows) {
  if ($rows["enabled"] == "true") {
    $payments_state = '';
    break;
  } else {
    $payments_state = 'disabled';
  }
}

$user_info = getUserInfo(null, $user_login);

?>

<?php if(empty($db_services_buyable)): ?>
  <section class="block">
    <h2>Купить привилегию</h2>
    <div class="well appear-bottom">
      <p>Активных привилегий не найдено.</p>
      <? if($is_user_admin) echo '<a href="profile?section=services&add" class="btn btn_primary">Добавить</a>'; ?>
    </div>
  </section>
<?php else: ?>
  <div class="buy-content">
    <div class="buy-content__left">
      <section class="block">
        <h2 class="block__title">Купить привилегию</h2>
        <div class="well">
          <? if($payments_state == "disabled"): ?>
            <h3 class="info-block">ВНИМАНИЕ!<br>На данный момент покупка привилегий на сайте приостановлена.<br>Обращайтесь напрямую в ЛС (контакты внизу сайта).</h3>
          <? endif; ?>
          <?php
            if ($is_user_logged) {
              if (isUserActive($user_info["id"], null)) {
                if ($user_info["service_nolimit"]) {
                  echo '
                    <p class="info-block">У вас уже активна <b>безлимитная</b> привилегия <b>'.getServiceName($user_info["service_id"]).'</b>.<br>Покупка новой <b>перезапишет</b> текущую!</p>
                  ';
                } else if (isset($user_info["service_end"]) && !empty($user_info["service_end"])) {
                  echo '
                    <p class="info-block">У вас уже активна привилегия <b>'.getServiceName($user_info["service_id"]).'</b>, которая истекает через <b>'.dateDiff(time(), strtotime($user_info["service_end"])).'</b>. Её можно продлить в <a href="profile?section=prolong" class="bordered">профиле</a>.<br>Покупка новой <b>перезапишет</b> текущую!</p>
                  ';
                }
              }
            }
          ?>
          <form id="buy_form" class="form" method="post">
            <label>Выберите привилегию</label>
            <select name="service_id" required>
              <?php
                foreach ($db_services_buyable as $rows) {
                  echo '<option value="'.$rows["id"].'">'.$rows["name"].'</option>';
                }
              ?>
            </select>
            <label>Выберите срок</label>
            <?php
              foreach ($db_services_buyable as $rows) {
                echo '<select name="service_days_'.$rows["id"].'">';
                  foreach (json_decode($rows["days"], true) as $days) {
                    if ($days["days"] == 0) {
                      $days_count = 'Навсегда';
                    } else {
                      $days_count = $days["days"].' дней';
                    }
                    echo '
                      <option value="'.$days["days"].'">'.$days_count.' - '.$days["price_rub"].' руб/'.$days["price_uah"].' грн.</option>
                    '; 
                  }
                echo '</select>';
              }
            ?>
            <label>Выберите систему оплаты</label>
            <select name="service_payment" required>
              <?php
                foreach ($db_payments as $key => $rows) {
                  if ($rows["enabled"] == "true") {
                    $enabled_state = '';
                    $enabled_desc = '';
                  } else {
                    $enabled_state = 'disabled';
                    $enabled_desc = ' (недоступно)';
                  }
                  if ($key == "InterKassa") {
                    //$pay_desc = "(для жителей Украины)";
                    $pay_desc = "";
                  }
                  if ($key == "LiqPay") {
                    //$pay_desc = "(для жителей Украины)";
                    $pay_desc = "";
                  }
                  echo '<option value="'.$key.'" '.$enabled_state.'>'.$key.$pay_desc.$enabled_desc.'</option>';
                }
              ?>
            </select>
            <label>Выберите валюту оплаты</label>
            <select name="currency">
              <option value="rub">рубли &#8381;</option>
              <option value="uah">гривны &#8372;</option>
            </select>
            <? if (!$is_user_logged): ?>
              <label>Выберите тип привязки</label>
              <select name="service_bind_type" required>
                <option value="nick_pass" selected>Ник + пароль</option>
                <option value="steam_pass">Steam ID + пароль</option>
              </select>
              <div id="nick_pass" class="form-label">
                <label>Ник на сервере</label>
                <input type="text" placeholder="Ваш ник на сервере" name="nick">
              </div>
              <div id="steam_pass" class="form-label" style="display:none;">
                <label>Укажите Steam ID</label>
                <input type="text" placeholder="Ваш Steam ID" name="steam_id">
              </div>
              <label>
                Придумайте пароль
                <br><small>Пароль будет использоваться для привязки привилегии в игре и для логина на сайте</small>
              </label>
              <input type="password" placeholder="Ваш пароль" name="password" required>
              <label>Укажите логин на сайте</label>
              <input type="text" placeholder="Ваш логин" name="login" required>
              <label>Укажите e-mail</label>
              <input type="email" name="email" placeholder="Ваш e-mail" required>
              <label>Укажите ваше имя</label>
              <input type="text" placeholder="Ваш имя" name="name" required>
            <? else: ?>
                <input type="hidden" name="user_id" value="<?= $user_info["id"] ?>" required>
                <input type="hidden" name="pass_check" value="<?= $user_info["password"] ?>" required>
            <? endif; ?>
            <label>Согласен с <a href="public-offer" target="_blank">публичной офертой</a> и <a href="privacy-policy" target="_blank">политикой конфиденциальности</a></label>
            <label class="switcher">
              <input type="checkbox" name="accept_policy" checked>
              <span class="slider round"></span>
            </label>
            <button class="btn btn_cta" type="submit" <?= $payments_state ?>>Купить</button>
          </form>
        </div>
      </section>
    </div>
    <div class="buy-content__right">
      <section class="block">
        <h2 class="block__title">Описание</h2>
        <?php foreach($db_services_buyable as $rows): ?>
          <div id="service_desc_<?=$rows["id"]?>">
            <? if(!empty($rows["images"])): ?>
              <div class="buy-content__img appear-right">
                <div class="carousel">
                  <? foreach(json_decode($rows["images"], true) as $image): ?>
                    <div class="carousel__img"><img src="<?=urlEncodeSpaces($image)?>" alt="Привилегия <?=$rows["name"]?>" class="buy-content" data-zoomable></div>
                  <? endforeach; ?>
                </div>
              </div>
            <? endif; ?>
            <div class="buy-content__description appear-right anim-delay-1">
              <div class="well well_primary">
                <h3 class="text-center"><?=$rows["name"]?></h3>
                <?php foreach(json_decode($rows["days"], true) as $days): ?>
                  <?php
                    if ($days["days"] == 0) {
                      $days_count = 'навсегда';
                    } else {
                      $days_count = 'за '.$days["days"].' дней';
                    }
                  ?>
                  <p><span class="label label-info"><?=$days["price_rub"]?> руб.</span> или <span class="label label-info"><?=$days["price_uah"]?> грн.</span> <?=$days_count?>.</p>
                <? endforeach; ?>
                <? if(isset($rows["description"])): ?>
                  <?=$rows["description"]?>
                <? else: ?>
                  <p>Описание не заполнено.</p>
                  <? if($is_user_admin): ?>
                    <a href="profile?section=services&edit=<?=$rows["id"]?>" class="btn">Заполнить</a>
                  <? endif; ?>
                <? endif; ?>
              </div>
            </div>
          </div>
        <? endforeach; ?>
      </section>
    </div>
  </div>
<?php endif; ?>

<? require_once("partials/footer.php"); ?>