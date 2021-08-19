<? require_once("partials/header.php"); ?>

<section class="block">
  <h2>Игровой чат за <?=date("d.m.Y")?></h2>
  <div class="well appear-bottom">
    <?php
      if (!empty($GLOBALS["ftp_login"]) && !empty($GLOBALS["ftp_pass"]) && !empty($GLOBALS["ftp_host"]) && $is_user_admin) {
        try {
          $chat_content = file_get_contents("ftp://".$GLOBALS["ftp_login"].":".$GLOBALS["ftp_pass"]."@".$GLOBALS["ftp_host"]."/cstrike/addons/amxmodx/logs/chatmanager/chatlog_".date("Ymd").".html");
          echo $chat_content;
        }
        catch (Exception $e) {
          print 'Error : ' . $e->getMessage();
        }
      } else {
        echo "Игровой чат доступен только для Администраторов сайта.";
      }
    ?>
  </div>
</section>

<? require_once("partials/footer.php"); ?>