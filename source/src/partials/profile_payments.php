<?php
  $db_payments = $GLOBALS["payments"];
  $site_url = $GLOBALS["site_url"];
?>

<section class="block">
  <h2 class="block__title">Настройки автооплаты</h2>
  <div class="well appear-bottom">
    <form id="edit_payments" method="post" class="form">
      <?php
        foreach (json_decode($db_payments, true) as $key => $rows) {
          if ($rows["enabled"] == "true") {
            $is_enabled = "checked";
          } else {
            $is_enabled = "";
          }
          echo '
            <label>Покупка через '.$key.'</label>
            <label class="switcher">
              <input type="checkbox" name="'.$key.'_enabled" '.$is_enabled.'>
              <span class="slider round"></span>
            </label>
            <label>'.$key.' кошелек</label>
            <input type="text" name="'.$key.'_purse" value="'.$rows["purse"].'" placeholder="Публичный ключ">
            <label>'.$key.' секретный ключ</label>
            <input type="text" name="'.$key.'_secret" value="'.$rows["secret"].'" placeholder="Секретный ключ">
            <p class="info-block">
              Обратный URL для всех взаимодействий:
              <span class="copy" data-copy="'.$site_url.'buy_process?'.$key.'" title="Нажми чтобы скопировать"><strong>'.$site_url.'buy_process?'.$key.'</strong>'.getSvg("img/icons/copy.svg").'</span>
              <br>
              Тип запроса: <strong>POST</strong>
            </p>
          ';
        }
      ?>
      <button type="submit" class="btn">Обновить</button>
    </form>
  </div>
</section>