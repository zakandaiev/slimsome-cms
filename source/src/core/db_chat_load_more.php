<?php

if(!isset($_POST["from"]) || empty($_POST["from"])) {
  header("Location: /404");
  exit();
}

require_once("db_connect.php");
require_once("db_settings.php");
require_once("core.php");
require_once("is_user_logged.php");
require_once("is_user_admin.php");
require_once("is_user_moder.php");

$from = $_POST["from"];
$body = '';

$db_chat_query = $pdo->query("
  SELECT * FROM (
    SELECT *, (
      SELECT coalesce(nick,login) FROM ".$prefix."_users WHERE id=t_chat.user_id) as nick,
      (CASE 
        WHEN (SELECT name FROM ".$prefix."_services WHERE id=(SELECT service_id FROM ".$prefix."_users WHERE id=t_chat.user_id AND service_id IS NOT NULL AND (service_nolimit IS TRUE OR CURRENT_TIMESTAMP < service_end)) AND enabled IS TRUE) IS NOT NULL
        THEN (SELECT name FROM ".$prefix."_services WHERE id=(SELECT service_id FROM ".$prefix."_users WHERE id=t_chat.user_id AND service_id IS NOT NULL AND (service_nolimit IS TRUE OR CURRENT_TIMESTAMP < service_end)) AND enabled IS TRUE)
        WHEN (SELECT isadmin FROM ".$prefix."_users WHERE id=t_chat.user_id) = 1
        THEN 'Администратор сайта'
        WHEN (SELECT ismoder FROM ".$prefix."_users WHERE id=t_chat.user_id) = 1
        THEN 'Модератор сайта'
        ELSE 'Пользователь'
      END) as position,
      (CASE 
        WHEN (SELECT user_avatar FROM ".$prefix."_services WHERE id=(SELECT service_id FROM ".$prefix."_users WHERE id=t_chat.user_id AND service_id IS NOT NULL AND (service_nolimit IS TRUE OR CURRENT_TIMESTAMP < service_end)) AND enabled IS TRUE) IS NOT NULL
        THEN (SELECT user_avatar FROM ".$prefix."_services WHERE id=(SELECT service_id FROM ".$prefix."_users WHERE id=t_chat.user_id AND service_id IS NOT NULL AND (service_nolimit IS TRUE OR CURRENT_TIMESTAMP < service_end)) AND enabled IS TRUE)
        ELSE 'img/no_avatar.jpg'
      END) as avatar,
      (CASE 
        WHEN refference IS NOT NULL
        THEN (SELECT coalesce(nick,login) FROM ".$prefix."_users WHERE id=(SELECT user_id FROM ".$prefix."_chat WHERE id=t_chat.refference))
      END) as refference_name
    FROM ".$prefix."_chat t_chat ORDER BY cdate DESC LIMIT ".$from.",30
  ) t1 ORDER BY t1.id -- reverse rows
");

$db_chat = $db_chat_query->fetchAll(PDO::FETCH_ASSOC);

if(empty($db_chat)) {
  echo json_encode(array("success" => 0));
  exit();
}

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
  $body .= '<div id="message_id_'.$rows["id"].'" class="chat__message">';
  $body .= '<img class="avatar" src="'.urlEncodeSpaces($rows["avatar"]).'" title="'.$rows["position"].'" alt="'.$rows["position"].'" data-name="'.$rows["nick"].'">';
  $body .= '
    <div class="message">
      <div class="info">
        <div class="author" title="'.$rows["position"].'" data-name="'.$rows["nick"].'">'.$rows["nick"].'</div>
        <div class="date">'.dateWhen(strtotime($rows["cdate"])). $delete_btn .'</div>
      </div>
      <div class="text">'.$refference . replaceSmiles($rows["message"]).'</div>
    </div>
  ';
  $body .= '</div>';
}

echo json_encode(array("success" => 1, "body" => $body));

?>