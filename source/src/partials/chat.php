<?php

require_once(dirname(__FILE__)."/../core/db_connect.php");
require_once(dirname(__FILE__)."/../core/db_settings.php");
require_once(dirname(__FILE__)."/../core/core.php");
require_once(dirname(__FILE__)."/../core/is_user_logged.php");
require_once(dirname(__FILE__)."/../core/is_user_admin.php");
require_once(dirname(__FILE__)."/../core/is_user_moder.php");

$db_chat = require_once(dirname(__FILE__)."/../core/db_chat.php");

if(!$is_user_logged) {
  $user_info = null;
} else {
  $user_info = getUserInfo(null, $_COOKIE["user_login"]);
}

?>

<div class="chat <?if($is_user_logged):?>chat_for-logged<?endif;?>">
  <div class="chat__messages">
    <? if(empty($db_chat)): ?>
      <img src="https://i.gifer.com/4ZOQ.gif">
      <p class="text-center">Смелых не нашлось. Будь первым! ;)</p>
    <? else: ?>
      <?php
        foreach ($db_chat as $rows) {
          if (isset($rows["refference"]) && !empty($rows["refference"])) {
            $refference = '<a href="#message_id_'.$rows["refference"].'" class="anchor">'.$rows["refference_name"].',</a> ';
          } else {
            $refference = '';
          }
          if ($is_user_admin || $is_user_moder) {
            $delete_btn = '<br><span data-del-chat="'.$rows["id"].'">удалить</span>';
          } else {
            $delete_btn = '';
          }
          echo '<div id="message_id_'.$rows["id"].'" class="chat__message">';
          echo '<img class="avatar" src="'.urlEncodeSpaces($rows["avatar"]).'" title="'.$rows["position"].'" alt="'.$rows["position"].'" data-name="'.$rows["nick"].'">';
          echo '
            <div class="message">
              <div class="info">
                <div class="author" title="'.$rows["position"].'" data-name="'.$rows["nick"].'">'.$rows["nick"].'</div>
                <div class="date">'.dateWhen(strtotime($rows["cdate"])). $delete_btn .'</div>
              </div>
              <div class="text">'.$refference . replaceSmiles($rows["message"]).'</div>
            </div>
          ';
          echo '</div>';
        }
      ?>
    <? endif; ?>
  </div>
  <? if(!$is_user_logged): ?>
    <div class="chat__write">
      <div class="text-center"><span data-open-login-form class="bordered">Авторизуйтесь</span>, чтобы отправлять сообщения.</div>
    </div>
  <? else: ?>
    <div class="chat__write">
      <p id="reply_info"></p>
      <form id="add_chat" method="post" class="form">
        <div class="chat__input">
          <input type="text" name="message" maxlength="100" placeholder="Введите сообщение..." required>
          <?= getSvg(dirname(__FILE__)."/../img/icons/smile.svg") ?>
          <div class="chat__smiles">
            <div class="smiles">
              <?php
                $smiles_dir = dirname(__FILE__)."/../img/smiles";
                if (file_exists($smiles_dir)) {
                  $smiles = glob($smiles_dir."/*.gif");
                  foreach ($smiles as $smile) {
                    echo '<span class="smiles__item"><img src="/img/smiles/'.basename($smile).'" data-filename="'.basename($smile).'" alt="'.substr(basename($smile), 0, -4).'"></span>';
                  }
                }
              ?>
            </div>
          </div>
        </div>
        <input type="submit" class="btn" value="Отправить">
        <input type="hidden" name="user_id" value="<?= $user_info["id"] ?>" required>
        <input type="hidden" name="pass_check" value="<?= $user_info["password"] ?>" required>
        <input type="hidden" name="refference" value="">
      </form>
    </div>
  <? endif; ?>
</div>