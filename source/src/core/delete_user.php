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

if (!isset($_POST["user_id"])) {
  echo json_encode(array("success" => 0, "error" => "Неверный параметр"));
  exit();
}

$user_id = $_POST["user_id"];

$db_services_del_query = $pdo->prepare("DELETE FROM ".$prefix."_users WHERE id=:user_id");
$db_services_del_query->bindParam(":user_id", $user_id);

// EXEC QUERY
try {
  $db_services_del_query->execute();
  if ($db_services_del_query->rowCount() == 1) {
    echo json_encode(array("success" => 1));
  } else {
    echo json_encode(array("error" => 0, "error" => "Неверные данные пользователя"));
  }
} catch(PDOException $error) { 
  echo json_encode(array("error" => 0, "error" => $error->getMessage()));
}

?>