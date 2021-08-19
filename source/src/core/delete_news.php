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

if (!isset($_POST["news_id"])) {
  echo json_encode(array("success" => 0, "error" => "Неверный параметр"));
  exit();
}

$news_id = $_POST["news_id"];

$db_news_del_query = $pdo->prepare("DELETE FROM ".$prefix."_news WHERE id=:news_id");
$db_news_del_query->bindParam(":news_id", $news_id);

// EXEC QUERY
try {
  $db_news_del_query->execute();
  if ($db_news_del_query->rowCount() == 1) {
    generateSitemapXml();
    echo json_encode(array("success" => 1));
  } else {
    echo json_encode(array("error" => 0, "error" => "Неверные данные новости"));
  }
} catch(PDOException $error) { 
  echo json_encode(array("error" => 0, "error" => $error->getMessage()));
}

?>