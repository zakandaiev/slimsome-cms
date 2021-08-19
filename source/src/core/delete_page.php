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

if (!isset($_POST["page_id"])) {
  echo json_encode(array("success" => 0, "error" => "Неверный параметр"));
  exit();
}

$page_id = $_POST["page_id"];

$page_id_query = $pdo->prepare("DELETE FROM ".$prefix."_pages WHERE id=:page_id");
$page_id_query->bindParam(":page_id", $page_id);

// EXEC QUERY
try {
  $page_id_query->execute();
  if ($page_id_query->rowCount() == 1) {
    generateSitemapXml();
    echo json_encode(array("success" => 1));
  } else {
    echo json_encode(array("error" => 0, "error" => "Неверные данные страницы"));
  }
} catch(PDOException $error) { 
  echo json_encode(array("error" => 0, "error" => $error->getMessage()));
}

?>