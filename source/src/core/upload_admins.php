<?php

require_once("db_connect.php");
require_once("db_settings.php");
require_once("core.php");
require_once("is_user_logged.php");
/*require_once("is_user_admin.php");
require_once("is_user_moder.php");*/

if (!$is_user_logged) {
  echo json_encode(array("success" => -1, "error" => "У вас нет прав"));
  exit();
}

if(!isset($_POST["sure"])) {
  header("Location: /404");
  exit();
}

$upload = uploadUsersIni(generateUsersIni());

if ($upload === true) {
  echo json_encode(array('success' => 1));
} else {
  echo json_encode(array('success' => 0, 'error' => $upload));
}

?>