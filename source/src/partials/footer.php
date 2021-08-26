<?php
  $db_socials_query = $GLOBALS["socials"];
?>

    </div>
  </div>

  <footer class="footer">
    <div class="container">
      <?php if(!empty($db_socials_query)): ?>
        <h2 class="footer__title">Контакты</h2>
        <div class="socials">
          <?php
            foreach (json_decode($db_socials_query) as $rows) {
              $blank = '';
              $icon_path = 'img/socials/'.$rows->icon;
              if ($rows->blank) {
                $blank = 'target="_blank" rel="noopener noreferrer"';
              }
              if(substr($rows->icon, -3) == "svg") {
                echo '<a href="'.$rows->url.'" class="socials__icon" '.$blank.'>'.getSvg($icon_path).'</a>';
              } else {
                echo '<a href="'.$rows->url.'" class="socials__icon" '.$blank.'><img src="'.urlEncodeSpaces($icon_path).'" alt="'.$rows->icon.'"></a>';
              }
            }
          ?>
        </div>
      <? endif; ?>
      <div class="footer__copy"><?= $GLOBALS["site_name"] ?> &copy; <?= date("Y") ?> Все права защищены</div>
      <div>
        <a href="privacy-policy">Политика конфиденциальности</a>. <a href="public-offer">Публичная оферта</a>
      </div>
      <div>
        E-mail администратора: <a href="mailto:<?= $GLOBALS["site_email"] ?>"><?= $GLOBALS["site_email"] ?></a>
      </div>
    </div>
  </footer>

  <? require_once("footer_scripts.php"); ?>

</body>

</html>