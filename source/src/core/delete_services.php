<?php

require_once("db_connect.php");
require_once("db_settings.php");
require_once("core.php");
require_once("is_user_logged.php");
require_once("is_user_admin.php");
require_once("is_user_moder.php");

if (!$is_user_admin) {
  echo json_encode(array("success" => -1, "error" => "У вас нет прав"));
  exit();
}

if (!isset($_POST["service_id"])) {
  echo json_encode(array("success" => 0, "error" => "Неверный параметр"));
  exit();
}

$service_id = $_POST["service_id"];

$db_services_del_query = $pdo->prepare("DELETE FROM ".$prefix."_services WHERE id=:service_id");
$db_services_del_query->bindParam(":service_id", $service_id);

// EXEC QUERY
try {
  $db_services_del_query->execute();
  if ($db_services_del_query->rowCount() == 1) {
    echo json_encode(array("success" => 1));
  } else {
    echo json_encode(array("error" => 0, "error" => "Неверные данные привилегии"));
  }
} catch(PDOException $error) { 
  echo json_encode(array("error" => 0, "error" => $error->getMessage()));
}

?>