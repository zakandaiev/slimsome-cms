<?php

if(empty($_POST["news_id"]) || empty($_POST["from"])) {
  header("Location: /404");
  exit();
}

require_once("db_connect.php");
require_once("db_settings.php");
require_once("core.php");
require_once("is_user_logged.php");
require_once("is_user_admin.php");
require_once("is_user_moder.php");

$news_id = $_POST["news_id"];
$from = $_POST["from"];
$body = '';

$db_comments_query = $pdo->prepare("
  SELECT *,
    (SELECT coalesce(nick,login) FROM ".$prefix."_users WHERE id=t_comments.author) as author_nick,
    (SELECT name FROM ".$prefix."_users WHERE id=t_comments.author) as author_name,
    (CASE 
      WHEN (SELECT name FROM ".$prefix."_services WHERE id=(SELECT service_id FROM ".$prefix."_users WHERE id=t_comments.author AND service_id IS NOT NULL AND (service_nolimit IS TRUE OR CURRENT_TIMESTAMP < service_end)) AND enabled IS TRUE) IS NOT NULL
      THEN (SELECT name FROM ".$prefix."_services WHERE id=(SELECT service_id FROM ".$prefix."_users WHERE id=t_comments.author AND service_id IS NOT NULL AND (service_nolimit IS TRUE OR CURRENT_TIMESTAMP < service_end)) AND enabled IS TRUE)
      WHEN (SELECT isadmin FROM ".$prefix."_users WHERE id=t_comments.author) = 1
      THEN 'Администратор сайта'
      WHEN (SELECT ismoder FROM ".$prefix."_users WHERE id=t_comments.author) = 1
      THEN 'Модератор сайта'
      ELSE 'Пользователь'
    END) as author_position,
    (CASE 
      WHEN (SELECT user_avatar FROM ".$prefix."_services WHERE id=(SELECT service_id FROM ".$prefix."_users WHERE id=t_comments.author AND service_id IS NOT NULL AND (service_nolimit IS TRUE OR CURRENT_TIMESTAMP < service_end)) AND enabled IS TRUE) IS NOT NULL
      THEN (SELECT user_avatar FROM ".$prefix."_services WHERE id=(SELECT service_id FROM ".$prefix."_users WHERE id=t_comments.author AND service_id IS NOT NULL AND (service_nolimit IS TRUE OR CURRENT_TIMESTAMP < service_end)) AND enabled IS TRUE)
      ELSE 'img/no_avatar.jpg'
    END) as author_avatar
  FROM ".$prefix."_comments t_comments
  WHERE news_id=:current_id
  ORDER BY cdate ASC
  LIMIT ".$from.",30;
");

$db_comments_query->bindParam(":current_id", $news_id);
$db_comments_query->execute();
$db_comments = $db_comments_query->fetchAll(PDO::FETCH_ASSOC);

if(empty($db_comments)) {
  echo json_encode(array("success" => 0));
  exit();
}

foreach ($db_comments as $rows) {
  if ($is_user_admin || $is_user_moder) {
    $delete_btn = '<br><span data-del-comment="'.$rows["id"].'">удалить</span>';
  } else {
    $delete_btn = '';
  }
  $body .= '<div class="comments__item">';
  $body .= '<div class="comments__avatar"><img class="avatar" src="'.urlEncodeSpaces($rows["author_avatar"]).'" title="'.$rows["author_position"].'" alt="'.$rows["author_position"].'"></div>';
  $body .= '
    <div class="comments__message">
      <div class="info">
        <div class="author" title="'.$rows["author_position"].'">'.$rows["author_nick"].'</div>
        <div class="date">'.dateWhen(strtotime($rows["cdate"])). $delete_btn .'</div>
      </div>
      <div class="text">'.replaceSmiles($rows["comment"]).'</div>
    </div>
  ';
  $body .= '</div>';
}

echo json_encode(array("success" => 1, "body" => $body));

?>