<?php

require_once("db_connect.php");
require_once("db_settings.php");
require_once("core.php");
require_once("is_user_logged.php");
require_once("is_user_admin.php");
require_once("is_user_moder.php");

if (!$is_user_admin && !$is_user_moder) {
  echo json_encode(array("success" => -1, "error" => "У вас нет прав"));
  exit();
}

if (!isset($_POST["comment_id"])) {
  echo json_encode(array("success" => 0, "error" => "Неверный параметр"));
  exit();
}

$comment_id = $_POST["comment_id"];

$comment_del_query = $pdo->prepare("DELETE FROM ".$prefix."_comments WHERE id=:comment_id");
$comment_del_query->bindParam(":comment_id", $comment_id);

// EXEC QUERY
try {
  $comment_del_query->execute();
  if ($comment_del_query->rowCount() == 1) {
    echo json_encode(array("success" => 1));
  } else {
    echo json_encode(array("error" => 0, "error" => "Неверные данные комментария"));
  }
} catch(PDOException $error) { 
  echo json_encode(array("error" => 0, "error" => $error->getMessage()));
}

?>