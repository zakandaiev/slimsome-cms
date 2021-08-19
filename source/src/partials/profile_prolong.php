<?php

$user_info = getUserInfo(null, $user_login);

$user_service_id = $user_info["service_id"];

$user_service_query = $pdo->prepare("SELECT * FROM ".$prefix."_services WHERE id=:service_id AND enabled IS TRUE LIMIT 1;");
$user_service_query->bindParam(":service_id", $user_service_id);
$user_service_query->execute();
$user_service = $user_service_query->fetch(PDO::FETCH_LAZY);

$db_payments = json_decode($GLOBALS["payments"], true);

foreach ($db_payments as $key => $rows) {
  if ($rows["enabled"] == "true") {
    $payments_state = '';
    break;
  } else {
    $payments_state = 'disabled';
  }
}

?>
<?php if (isUserActive($user_info["id"], null)): ?>
  <?php if ($user_info["service_nolimit"]): ?>
    <section class="block">
      <h2 class="block__title">Продление невозможно</h2>
      <div class="well appear-bottom">
        <p>У вас активна <b>безлимитная</b> привилегия <b><?= getServiceName($user_service_id) ?></b> <img src="/img/smiles/cool.gif" alt="cool"></p>
      </div>
    </section>
  <? elseif (isset($user_info["service_end"]) && !empty($user_info["service_end"])): ?>
    <section class="block">
      <h2 class="block__title">Продление привилегии: <?= getServiceName($user_service_id) ?></h2>
      <div class="well appear-bottom">
        <? if($payments_state == "disabled"): ?>
          <h3 class="info-block">ВНИМАНИЕ!<br>На данный момент продление привилегий на сайте приостановлено.<br>Обращайтесь напрямую в ЛС (контакты внизу сайта).</h3>
          <br>
        <? endif; ?>
        <? if(empty($user_service)): ?>
          <h3 class="info-block">ВНИМАНИЕ!<br>Продление данной привилегии приостановлено.</h3>
        <? $payments_state = 'disabled';endif; ?>
        <p>У вас активна привилегия <b><?= getServiceName($user_service_id) ?></b>, которая истекает через <b><?= dateDiff(time(), strtotime($user_info["service_end"])) ?></b>.</p>
        <form id="service_prolong" method="post" class="form">
          <label>Выберите срок</label>
          <select name="service_days">
            <?php
              foreach (json_decode($user_service["days"], true) as $days) {
                if ($days["days"] == 0) {
                  $days_count = 'Навсегда';
                } else {
                  $days_count = $days["days"].' дней';
                }
                echo '
                  <option value="'.$days["days"].'">'.$days_count.' - '.$days["price_rub"].' руб/'.$days["price_uah"].' грн.</option>
                '; 
              }
            ?>
          </select>
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
          <label>Согласен с <a href="public-offer" target="_blank">публичной офертой</a> и <a href="privacy-policy" target="_blank">политикой конфиденциальности</a></label>
          <label class="switcher">
            <input type="checkbox" name="accept_policy" checked>
            <span class="slider round"></span>
          </label>
          <input type="hidden" name="user_id" value="<?= $user_info["id"] ?>" required>
          <input type="hidden" name="pass_check" value="<?= $user_info["password"] ?>" required>
          <button type="submit" class="btn btn_cta" <?= $payments_state ?>>Продлить</button>
        </form>
      </div>
    </section>
  <? else: ?>
    <section class="block">
      <h2 class="block__title">Продление невозможно</h2>
      <div class="well appear-bottom">
        <p>Привилегий нет.</p>
        <a href="buy" class="btn btn_cta">Купить</a>
      </div>
    </section>
  <? endif; ?>
<? else: ?>
  <section class="block">
    <h2 class="block__title">Продление невозможно</h2>
    <div class="well appear-bottom">
      <p>Привилегий нет.</p>
      <a href="buy" class="btn btn_cta">Купить</a>
    </div>
  </section>
<? endif; ?>